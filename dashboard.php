<?php
// dashboard.php
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Monitoring Produksi</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; }
        .btn {
            display: inline-block;
            padding: 20px 40px;
            margin: 20px;
            font-size: 24px;
            font-weight: bold;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 10px;
        }
        .btn:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h1>Monitoring Produksi</h1>
    <a href="monitoring/line_L.php" class="btn">Line L</a>
    <a href="monitoring/line_G.php" class="btn">Line G</a>
    <a href="monitoring/line_F.php" class="btn">Line F</a>
    <a href="input_prosesplan.php" class="btn">Input Plan</a>
</body>
</html>
