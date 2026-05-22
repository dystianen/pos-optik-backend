<!DOCTYPE html>
<html>

<head>
  <title>Struk Penjualan</title>
  <style>
    body {
      font-family: monospace;
      width: 300px;
      margin: auto;
    }

    hr {
      border: none;
      border-top: 1px dashed #000;
    }

    .center {
      text-align: center;
    }

    .right {
      text-align: right;
    }
  </style>
</head>

<body onload="window.print()">

  <div class="center">
    <strong>OPTIK FIQRI</strong><br>
    Jl. Raya Pacet No.25, Njarum, Pandanarum, Kec. Pacet, Kabupaten Mojokerto, Jawa Timur 61374<br>
    Telp: 0822-3237-4041
  </div>

  <hr>

  <p>
    Order: <?= $order['order_id'] ?><br>
    Tanggal: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?><br>
    Customer: <?= $order['customer_name'] ?>
  </p>

  <hr>

  <?php foreach ($items as $item): ?>
    <?= $item['product_name'] ?>
    <?= $item['variant_name'] ? '(' . $item['variant_name'] . ')' : '' ?><br>
    <?= $item['quantity'] ?> x <?= number_format($item['price']) ?>
    <span style="float:right">
      <?= number_format($item['quantity'] * $item['price']) ?>
    </span>
    <br>
  <?php endforeach ?>

  <hr>


  <p class="right">
    <strong>Total: <?= number_format($order['grand_total']) ?></strong>
  </p>

  <hr>

  <div class="center">
    Terima Kasih 🙏<br>
    Barang yang sudah dibeli<br>
    tidak dapat dikembalikan
  </div>

</body>

</html>