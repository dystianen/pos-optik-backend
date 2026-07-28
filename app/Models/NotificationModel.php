<?php

namespace App\Models;

use CodeIgniter\Model;
use Ramsey\Uuid\Uuid;

class NotificationModel extends Model
{
    protected $table      = 'notifications';
    protected $primaryKey = 'notification_id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'type',
        'message',
        'related_id',
        'is_read',
    ];

    protected $beforeInsert = ['generateUUID'];

    protected function generateUuid(array $data)
    {
        $data['data']['notification_id'] = service('uuid')->uuid4()->toString();
        return $data;
    }

    // Ambil notifikasi terbaru, default hanya yang belum dibaca
    public function getNotifications($onlyUnread = true, $limit = 10, $types = [])
    {
        $builder = $this->orderBy('created_at', 'DESC');
        if ($onlyUnread) {
            $builder->where('is_read', 0);
        }
        if (!empty($types)) {
            $builder->whereIn('type', $types);
        }
        return $this->findAll($limit);
    }

    /* hitung unread */
    public function countUnread($types = [])
    {
        $builder = $this->where('is_read', 0);
        if (!empty($types)) {
            $builder->whereIn('type', $types);
        }
        return $builder->countAllResults();
    }

    // Tandai notifikasi sebagai dibaca
    public function markAsRead($id)
    {
        return $this->update($id, ['is_read' => 1]);
    }

    // Tambahkan notifikasi baru
    public function addNotification($type, $message, $related_id = null)
    {
        return $this->insert([
            'type'       => $type,
            'message'    => $message,
            'related_id' => $related_id,
            'is_read'    => 0,
        ]);
    }
}
