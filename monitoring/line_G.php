<?php
include '../db.php';

date_default_timezone_set('Asia/Jakarta');

$line = 'G'; // bisa diubah sesuai kebutuhan

// === Fungsi hitung plan now ===
function hitungPlanNow($lot_size, $ct_per_case, $shift_start = '07:00', $shift_end = '16:10') {
    $now = new DateTime();
    $start = new DateTime($shift_start);

    // Hitung menit berjalan
    $menit_berjalan = ($now->getTimestamp() - $start->getTimestamp()) / 60;
    if ($menit_berjalan <= 0) return 0;

    // Jadwal break
    $breaks = [
        ['start' => '10:00', 'durasi' => 10],
        ['start' => '11:40', 'durasi' => 50],
    ];
    if ($now > new DateTime('16:00')) {
        $breaks[] = ['start' => '16:00', 'durasi' => 10];
    }

    // Hitung total break yang sudah lewat
    $total_break = 0;
    foreach ($breaks as $b) {
        $break_start = new DateTime($b['start']);
        if ($now > $break_start) {
            $break_end = clone $break_start;
            $break_end->modify("+{$b['durasi']} minutes");
            if ($now >= $break_end) {
                $total_break += $b['durasi'];
            } else {
                $total_break += ($now->getTimestamp() - $break_start->getTimestamp()) / 60;
            }
        }
    }

    // Menit efektif kerja
    $menit_efektif = $menit_berjalan - $total_break;

    // Menit per lot
    $menit_per_lot = $lot_size * $ct_per_case;

    // Plan now (dalam case)
    $plan_now_lot = floor($menit_efektif / $menit_per_lot);
    return $plan_now_lot * $lot_size;
}

// === Jika request dari AJAX (input scan) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kode'])) {
    $kode = trim($_POST['kode']);
    if ($kode !== '') {
        $stmt = $pdo->prepare("INSERT INTO tb_detailprod (tgll, line, kode, qty) VALUES (CURRENT_DATE, :line, :kode, 1)");
        $stmt->execute(['line' => $line, 'kode' => $kode]);
    }

    // Ambil actual terbaru
    $stmt2 = $pdo->prepare("SELECT COUNT(*) AS actual FROM tb_detailprod WHERE tgll = CURRENT_DATE AND line = :line");
    $stmt2->execute(['line' => $line]);
    $actual = $stmt2->fetch(PDO::FETCH_ASSOC)['actual'];

    // Ambil plan data
    $stmt3 = $pdo->prepare("SELECT plan, ct, jam_kerja FROM tb_prosesplan WHERE tgl = CURRENT_DATE AND line = :line LIMIT 1");
    $stmt3->execute(['line' => $line]);
    $planData = $stmt3->fetch(PDO::FETCH_ASSOC);

    $totalPlan = $planData['plan'] ?? 0;
    $ct = $planData['ct'] ?? 0;
    $jamKerja = $planData['jam_kerja'] ?? 0;
    $lot_size = $totalPlan > 0 ? ($totalPlan / floor(($jamKerja * 60) / ($ct))) : 1;
    $planNow = hitungPlanNow($lot_size, $ct);
    $balance = $actual - $planNow;

    echo json_encode([
        'actual' => $actual,
        'planNow' => $planNow,
        'balance' => ($balance >= 0 ? "+" : "") . $balance
    ]);
    exit;
}

// === Ambil data awal untuk tampilan ===
$stmt = $pdo->prepare("SELECT plan, ct, jam_kerja FROM tb_prosesplan WHERE tgl = CURRENT_DATE AND line = :line LIMIT 1");
$stmt->execute(['line' => $line]);
$planData = $stmt->fetch(PDO::FETCH_ASSOC);

$totalPlan = $planData['plan'] ?? 0;
$ct = $planData['ct'] ?? 0;
$jamKerja = $planData['jam_kerja'] ?? 0;
$lot_size = $totalPlan > 0 ? ($totalPlan / floor(($jamKerja * 60) / ($ct))) : 1;
$planNow = hitungPlanNow($lot_size, $ct);

$stmt2 = $pdo->prepare("SELECT COUNT(*) AS actual FROM tb_detailprod WHERE tgll = CURRENT_DATE AND line = :line");
$stmt2->execute(['line' => $line]);
$actual = $stmt2->fetch(PDO::FETCH_ASSOC)['actual'];

$balance = $actual - $planNow;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Monitoring Line <?php echo $line; ?></title>
    <style>
        h1 {
            text-align: center;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: white;
        }
        table {
            width: 100%;
            height: 100vh;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 2px solid black;
            text-align: center;
            vertical-align: middle;
        }
        .header {
            font-size: 2em;
            color: white;
            padding: 3px;
        }
        .total-plan {
            background-color: #0047ab; /* biru */
        }
        .plan-now {
            background-color: #ffb300; /* kuning */
        }
        .actual {
            background-color: #00a651; /* hijau */
        }
        .balance {
            background-color: #d32f2f; /* merah */
        }
        .value {
            font-size: 8em;
            font-weight: bold;
        }
        .scan-input {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 5px;
            height: 5px;
            font-size: 5px;
        }
    </style>
</head>

<body>
    <h1>Monitoring Line <?php echo $line; ?></h1>
    <!-- Tabel Monitoring -->

    <table>
    <tr>
        <th class="header total-plan">TOTAL PLAN</th>
        <th class="header plan-now">PLAN NOW</th>
    </tr>
    <tr>
        <td class="value" style="color:#0047ab;"><?php echo $totalPlan; ?></td>
        <td class="value" style="color:#ffb300;"><?php echo round($planNow); ?></td>
    </tr>
    <tr>
        <th class="header actual">ACTUAL</th>
        <th class="header balance">BALANCE</th>
    </tr>
    <tr>
        <td class="value" style="color:black;"><?php echo $actual; ?></td>
        <td class="value" style="color:#ff0000;"><?php echo round($balance); ?></td>

    </tr>
</table>




     <!-- Form scan barcode (kecil di pojok) -->
    <form method="POST" style="position:absolute; top:10px; right:10px;">
        <input type="text" name="barcode" id="barcode" class="scan-input" autofocus>
    </form>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function(){
        $("#scanInput").on("change", function(){
            let kode = $(this).val();
            $.post("", {kode: kode}, function(res){
                $("#actual").text(res.actual);
                $("#planNow").text(res.planNow);
                $("#balance").text(res.balance);
            }, "json");
            $(this).val("").focus();
        });
    });
    </script>
</body>

</html>
