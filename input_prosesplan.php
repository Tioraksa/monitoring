<?php
// db.php - koneksi database
$host = "localhost";
$dbname = "db_engine";
$user = "postgres";
$pass = "postgres"; // ganti sesuai password PostgreSQL

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// CREATE
if (isset($_POST['add'])) {
    $line = $_POST['line'];
    $plan = $_POST['plan'];
    $ct = $_POST['ct'];
    $jam_kerja_jam = $_POST['jam_kerja'];
    $jam_kerja_menit = $jam_kerja_jam * 60;

    $stmt = $pdo->prepare("INSERT INTO tb_prosesplan (line, plan, ct, jam_kerja) VALUES (?, ?, ?, ?)");
    $stmt->execute([$line, $plan, $ct, $jam_kerja_menit]);
    header("Location: input_prosesplan.php");
    exit;
}

// UPDATE
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $line = $_POST['line'];
    $plan = $_POST['plan'];
    $ct = $_POST['ct'];
    $jam_kerja_jam = $_POST['jam_kerja'];
    $jam_kerja_menit = $jam_kerja_jam * 60;

    $stmt = $pdo->prepare("UPDATE tb_prosesplan SET line=?, plan=?, ct=?, jam_kerja=? WHERE id=?");
    $stmt->execute([$line, $plan, $ct, $jam_kerja_menit, $id]);
    header("Location: input_prosesplan.php");
    exit;
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM tb_prosesplan WHERE id=?");
    $stmt->execute([$id]);
    header("Location: input_prosesplan.php");
    exit;
}

// Ambil data
$data = $pdo->query("SELECT * FROM tb_prosesplan ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Proses Plan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="p-4">

    <h2>Data Proses Plan</h2>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Plan</button>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Line</th>
                <th>Plan</th>
                <th>CT (menit)</th>
                <th>Jam Kerja (menit)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['line'] ?></td>
                    <td><?= $row['plan'] ?></td>
                    <td><?= $row['ct'] ?></td>
                    <td><?= $row['jam_kerja'] ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Hapus data ini?')" class="btn btn-danger btn-sm">Hapus</a>
                    </td>
                </tr>

                <!-- Modal Edit -->
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="post">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Plan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label>Line</label>
                                        <input type="text" name="line" value="<?= $row['line'] ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Plan</label>
                                        <input type="number" name="plan" value="<?= $row['plan'] ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>CT (menit)</label>
                                        <input type="number" name="ct" value="<?= $row['ct'] ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label>Jam Kerja (jam)</label>
                                        <input type="number" name="jam_kerja" value="<?= $row['jam_kerja'] / 60 ?>" class="form-control" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update" class="btn btn-success">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal Add -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label>Line</label>
                            <select name="line" class="form-control" required>
                                <option value="">-- Pilih Line --</option>
                                <option value="L">Line L</option>
                                <option value="G">Line G</option>
                                <option value="F">Line F</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Plan</label>
                            <input type="number" name="plan" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>CT (menit)</label>
                            <input type="number" name="ct" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Jam Kerja (jam)</label>
                            <input type="number" name="jam_kerja" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add" class="btn btn-primary">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>