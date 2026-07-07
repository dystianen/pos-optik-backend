<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Evaluasi akurasi sistem rekomendasi (Content-Based Filtering)
 * menggunakan metode Leave-One-Out Cross Validation.
 *
 * Cara kerja:
 * 1. Ambil customer yang punya >= 2 transaksi "completed".
 * 2. Urutkan pembelian berdasarkan waktu, lalu SEMBUNYIKAN (hold-out)
 *    pembelian TERAKHIR sebagai ground truth.
 * 3. Pembelian sebelum hold-out dipakai sebagai histori (user profile),
 *    dan pembelian tepat sebelum hold-out dipakai sebagai "produk anchor"
 *    (meniru user yang sedang melihat halaman produk tersebut).
 * 4. Jalankan logika rekomendasi (disalin dari controller Anda),
 *    lalu cek apakah produk hold-out muncul di Top-K hasil rekomendasi.
 * 5. Ulangi untuk semua customer, lalu hitung Hit Rate, Precision@K, Recall@K.
 *
 * CARA PAKAI:
 *   php spark recommendation:evaluate
 *   php spark recommendation:evaluate --k=10 --min-orders=2
 *   php spark recommendation:evaluate --export=writable/evaluation_results.csv
 *
 * PENTING - SESUAIKAN DENGAN SKEMA ANDA:
 *   - Method getCompletedStatusId() menebak nama tabel status ("order_statuses")
 *     dan kolom kode ("completed"). Ganti sesuai StatusModel/tabel asli Anda.
 *   - Nama tabel/kolom lain (orders, order_items, product_attribute_values,
 *     products) disamakan dengan controller apiProductRecommendations Anda.
 */
class EvaluateRecommendations extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'recommendation:evaluate';
    protected $description = 'Evaluasi akurasi sistem rekomendasi CBF menggunakan metode Leave-One-Out.';

    protected $usage   = 'recommendation:evaluate [options]';
    protected $options = [
        '--k'          => 'Jumlah Top-N rekomendasi yang diuji (default: 10)',
        '--min-orders' => 'Minimal jumlah order completed per customer agar diikutkan (default: 2)',
        '--limit'      => 'Batas jumlah customer yang diuji, kosongkan untuk semua (default: semua)',
        '--export'     => 'Path file CSV untuk menyimpan hasil detail per customer (opsional)',
    ];

    protected $db;

    public function run(array $params)
    {
        $this->db = Database::connect();

        $topK       = (int) (CLI::getOption('k') ?? 10);
        $minOrders  = (int) (CLI::getOption('min-orders') ?? 2);
        $limitOpt   = CLI::getOption('limit');
        $exportPath = CLI::getOption('export');

        CLI::write('=== Evaluasi Sistem Rekomendasi (Leave-One-Out) ===', 'yellow');
        CLI::write("Top-K            : {$topK}");
        CLI::write("Min. order/cust. : {$minOrders}");
        CLI::newLine();

        $completedStatusId = $this->getCompletedStatusId();

        $customerRows = $this->db->table('orders')
            ->select('customer_id')
            ->where('status_id', $completedStatusId)
            ->where('deleted_at', null)
            ->groupBy('customer_id')
            ->having('COUNT(order_id) >=', $minOrders)
            ->get()
            ->getResultArray();

        $customerIds = array_column($customerRows, 'customer_id');

        if ($limitOpt) {
            $customerIds = array_slice($customerIds, 0, (int) $limitOpt);
        }

        if (empty($customerIds)) {
            CLI::error('Tidak ada customer yang memenuhi syarat minimal order.');
            return;
        }

        CLI::write('Jumlah customer kandidat uji: ' . count($customerIds));
        CLI::newLine();

        $results      = [];
        $hitCount     = 0;
        $softHitCount = 0;
        $totalTested  = 0;

        foreach ($customerIds as $customerId) {
            $eval = $this->evaluateCustomer((int) $customerId, $completedStatusId, $topK);

            if ($eval === null) {
                continue; // dilewati: data histori/atribut tidak cukup
            }

            $results[] = $eval;
            $totalTested++;
            $hitCount     += $eval['hit'] ? 1 : 0;
            $softHitCount += $eval['soft_hit'] ? 1 : 0;

            $status = $eval['hit'] ? CLI::color('HIT', 'green') : CLI::color('MISS', 'red');
            CLI::write("Customer #{$eval['customer_id']} -> held-out produk #{$eval['held_out_id']} -> {$status} (posisi: " . ($eval['rank'] ?? '-') . ')');
        }

        CLI::newLine();

        if ($totalTested === 0) {
            CLI::error('Tidak ada customer yang berhasil dievaluasi. Cek data atribut produk / histori transaksi.');
            return;
        }

        $hitRate     = $hitCount / $totalTested;
        $softHitRate = $softHitCount / $totalTested;
        // Karena hanya ada 1 item ground-truth per customer:
        // Precision@K = hit / K, Recall@K = hit / 1 (setara dengan Hit Rate)
        $precisionAtK = $hitRate / $topK;
        $recallAtK    = $hitRate;

        CLI::write('=== HASIL AGREGAT ===', 'yellow');
        CLI::write("Total customer diuji        : {$totalTested}");
        CLI::write("Hit Rate@{$topK} (exact match) : " . round($hitRate * 100, 2) . '%');
        CLI::write("Hit Rate@{$topK} (kategori)    : " . round($softHitRate * 100, 2) . '%');
        CLI::write("Precision@{$topK}              : " . round($precisionAtK * 100, 2) . '%');
        CLI::write("Recall@{$topK}                 : " . round($recallAtK * 100, 2) . '%');

        if ($exportPath) {
            $this->exportCsv($results, $exportPath);
            CLI::write("Detail hasil disimpan ke: {$exportPath}", 'green');
        }
    }

    /**
     * Evaluasi satu customer dengan metode Leave-One-Out.
     */
    protected function evaluateCustomer(int $customerId, int $completedStatusId, int $topK): ?array
    {
        $purchases = $this->db->table('order_items oi')
            ->select('oi.product_id, o.order_type, o.created_at')
            ->join('orders o', 'o.order_id = oi.order_id')
            ->where('o.customer_id', $customerId)
            ->where('o.status_id', $completedStatusId)
            ->where('o.deleted_at', null)
            ->orderBy('o.created_at', 'ASC')
            ->get()
            ->getResultArray();

        if (count($purchases) < 2) {
            return null;
        }

        // Hold-out: pembelian TERAKHIR sebagai ground truth
        $heldOut   = array_pop($purchases);
        $heldOutId = (int) $heldOut['product_id'];

        $historyRows = $purchases;
        $anchor      = end($historyRows);
        $anchorId    = (int) $anchor['product_id'];
        $historyIds  = array_values(array_unique(array_column($historyRows, 'product_id')));

        $baseVector = $this->getProductVector($anchorId);
        if (empty($baseVector)) {
            return null;
        }

        $heldOutVector = $this->getProductVector($heldOutId);
        if (empty($heldOutVector)) {
            return null;
        }

        $userVector = [];
        $posVector  = [];
        $hasPosData = false;

        if (!empty($historyIds)) {
            $attrRows = $this->db->table('product_attribute_values pav')
                ->select('pav.attribute_id, pav.value, o.order_type')
                ->join('order_items oi', 'oi.product_id = pav.product_id')
                ->join('orders o', 'o.order_id = oi.order_id')
                ->whereIn('pav.product_id', $historyIds)
                ->where('o.customer_id', $customerId)
                ->where('o.status_id', $completedStatusId)
                ->where('pav.deleted_at', null)
                ->get()
                ->getResultArray();

            foreach ($attrRows as $row) {
                $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
                $userVector[$key] = ($userVector[$key] ?? 0) + 1;

                if ($row['order_type'] === 'offline') {
                    $hasPosData = true;
                    $posVector[$key] = ($posVector[$key] ?? 0) + 1;
                }
            }
        }

        $excludeIds = array_unique(array_merge($historyIds, [$anchorId]));

        $candidateRows = $this->db->table('products p')
            ->select('p.product_id')
            ->where('p.deleted_at', null)
            ->whereNotIn('p.product_id', $excludeIds)
            ->get()
            ->getResultArray();

        $candidateIds = array_column($candidateRows, 'product_id');
        if (empty($candidateIds)) {
            return null;
        }

        $allAttrRows = $this->db->table('product_attribute_values pav')
            ->select('pav.product_id, pav.attribute_id, pav.value')
            ->whereIn('pav.product_id', $candidateIds)
            ->where('pav.deleted_at', null)
            ->get()
            ->getResultArray();

        $vectorByProduct = [];
        foreach ($allAttrRows as $row) {
            $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
            $vectorByProduct[$row['product_id']][$key] = 1;
        }

        // Bobot sama seperti di controller apiProductRecommendations
        $wBase = 0.40;
        $wUser = $hasPosData ? 0.40 : 0.60;
        $wPos  = $hasPosData ? 0.20 : 0.00;

        $scored = [];
        foreach ($candidateIds as $pid) {
            if (!isset($vectorByProduct[$pid])) {
                continue;
            }
            $candidateVec = $vectorByProduct[$pid];

            $cbfScore  = $this->cosineSimilarity($baseVector, $candidateVec);
            $userScore = empty($userVector) ? 0.0 : $this->cosineSimilarity($userVector, $candidateVec);
            $posScore  = $hasPosData ? $this->cosineSimilarity($posVector, $candidateVec) : 0.0;

            $finalScore = ($wBase * $cbfScore) + ($wUser * $userScore) + ($wPos * $posScore);

            if ($finalScore > 0) {
                $scored[] = [
                    'product_id' => $pid,
                    'score'      => $finalScore,
                    'vector'     => $candidateVec,
                ];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);
        $topResults = array_slice($scored, 0, $topK);

        // Exact match: apakah produk hold-out ada di Top-K?
        $rank = null;
        foreach ($topResults as $i => $item) {
            if ((int) $item['product_id'] === $heldOutId) {
                $rank = $i + 1;
                break;
            }
        }
        $hit = $rank !== null;

        // Soft/category match: overlap atribut >= 50% dengan produk hold-out
        $softHit = false;
        foreach ($topResults as $item) {
            $overlap = count(array_intersect_key($item['vector'], $heldOutVector));
            $union   = count(array_unique(array_merge(array_keys($item['vector']), array_keys($heldOutVector))));
            $sim     = $union > 0 ? $overlap / $union : 0;
            if ($sim >= 0.5) {
                $softHit = true;
                break;
            }
        }

        return [
            'customer_id' => $customerId,
            'anchor_id'   => $anchorId,
            'held_out_id' => $heldOutId,
            'hit'         => $hit,
            'soft_hit'    => $softHit,
            'rank'        => $rank,
        ];
    }

    protected function getProductVector(int $productId): array
    {
        $rows = $this->db->table('product_attribute_values pav')
            ->select('pav.attribute_id, pav.value')
            ->where('pav.product_id', $productId)
            ->where('pav.deleted_at', null)
            ->get()
            ->getResultArray();

        $vector = [];
        foreach ($rows as $row) {
            $key = $row['attribute_id'] . '::' . strtolower(trim($row['value']));
            $vector[$key] = 1;
        }

        return $vector;
    }

    /**
     * Cosine similarity antar dua associative vector.
     * Logikanya sama persis dengan yang dipakai di RecommendationController.
     */
    protected function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dot = 0.0;
        foreach ($vecA as $key => $valA) {
            if (isset($vecB[$key])) {
                $dot += $valA * $vecB[$key];
            }
        }

        $normA = sqrt(array_sum(array_map(fn ($v) => $v * $v, $vecA)));
        $normB = sqrt(array_sum(array_map(fn ($v) => $v * $v, $vecB)));

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dot / ($normA * $normB);
    }

    /**
     * SESUAIKAN dengan tabel/model status pada project Anda.
     * Contoh jika Anda sudah punya StatusModel seperti di controller asli:
     *   $statusModel = new \App\Models\StatusModel();
     *   return $statusModel->getIdByCode(\Config\OrderStatus::COMPLETED);
     */
    protected function getCompletedStatusId(): int
    {
        $row = $this->db->table('order_statuses')
            ->select('status_id')
            ->where('status_code', 'completed') // sesuaikan value code di tabel Anda
            ->get()
            ->getRowArray();

        if (! $row) {
            CLI::error('Status "completed" tidak ditemukan. Sesuaikan method getCompletedStatusId().');
            exit(1);
        }

        return (int) $row['status_id'];
    }

    protected function exportCsv(array $results, string $path): void
    {
        $fp = fopen($path, 'w');
        fputcsv($fp, ['customer_id', 'anchor_product_id', 'held_out_product_id', 'hit_exact', 'hit_kategori', 'posisi_di_top_k']);

        foreach ($results as $r) {
            fputcsv($fp, [
                $r['customer_id'],
                $r['anchor_id'],
                $r['held_out_id'],
                $r['hit'] ? 'YA' : 'TIDAK',
                $r['soft_hit'] ? 'YA' : 'TIDAK',
                $r['rank'] ?? '-',
            ]);
        }

        fclose($fp);
    }
}