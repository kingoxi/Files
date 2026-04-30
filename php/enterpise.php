<?php
// ===============================
// LOCAL VERİTABANI AYARLARI
// ===============================

$host = "localhost";
$username = "root";
$password = "";
$dbname = "sinav";
$charset = "utf8mb4";
$collate = "utf8mb4_general_ci";

$mesaj = "";
$hata = "";
$duzenlenecek = null;

try {
    // 1. Önce sadece MySQL sunucusuna bağlanıyoruz
    // Çünkü sinav veritabanı henüz oluşmamış olabilir.
    $pdo = new PDO("mysql:host=$host;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Veritabanı yoksa oluştur
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `$dbname`
        CHARACTER SET $charset
        COLLATE $collate
    ");

    // 3. Oluşturulan veritabanına bağlan
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 4. Tablo yoksa oluştur
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS personel (
            personel_id INT AUTO_INCREMENT PRIMARY KEY,
            ad_soyad VARCHAR(150) NOT NULL,
            ise_baslama DATE NOT NULL,
            maas DECIMAL(10,2) NOT NULL,
            departman_adi VARCHAR(100) NOT NULL
        )
    ");

} catch (PDOException $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// ===============================
// GÜVENLİ YAZDIRMA FONKSİYONU
// ===============================

function temizle($veri)
{
    return htmlspecialchars($veri, ENT_QUOTES, "UTF-8");
}

// ===============================
// EKLEME / GÜNCELLEME / SİLME
// ===============================

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $islem = $_POST["islem"] ?? "";

    // PERSONEL EKLE
    if ($islem === "ekle") {
        $ad_soyad = trim($_POST["ad_soyad"] ?? "");
        $tarih = trim($_POST["tarih"] ?? "");
        $maas = trim($_POST["maas"] ?? "");
        $departman = trim($_POST["departman"] ?? "");

        if ($ad_soyad === "" || $tarih === "" || $maas === "" || $departman === "") {
            $hata = "Lütfen tüm alanları doldurun.";
        } elseif (!is_numeric($maas) || $maas <= 0) {
            $hata = "Maaş geçerli bir sayı olmalıdır.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO personel 
                (ad_soyad, ise_baslama, maas, departman_adi)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $ad_soyad,
                $tarih,
                $maas,
                $departman
            ]);

            header("Location: " . $_SERVER["PHP_SELF"] . "?durum=eklendi");
            exit;
        }
    }

    // PERSONEL GÜNCELLE
    if ($islem === "guncelle") {
        $personel_id = intval($_POST["personel_id"] ?? 0);
        $ad_soyad = trim($_POST["ad_soyad"] ?? "");
        $tarih = trim($_POST["tarih"] ?? "");
        $maas = trim($_POST["maas"] ?? "");
        $departman = trim($_POST["departman"] ?? "");

        if ($personel_id <= 0) {
            $hata = "Geçersiz personel ID.";
        } elseif ($ad_soyad === "" || $tarih === "" || $maas === "" || $departman === "") {
            $hata = "Lütfen tüm alanları doldurun.";
        } elseif (!is_numeric($maas) || $maas <= 0) {
            $hata = "Maaş geçerli bir sayı olmalıdır.";
        } else {
            $stmt = $pdo->prepare("
                UPDATE personel SET
                    ad_soyad = ?,
                    ise_baslama = ?,
                    maas = ?,
                    departman_adi = ?
                WHERE personel_id = ?
            ");

            $stmt->execute([
                $ad_soyad,
                $tarih,
                $maas,
                $departman,
                $personel_id
            ]);

            header("Location: " . $_SERVER["PHP_SELF"] . "?durum=guncellendi");
            exit;
        }
    }

    // PERSONEL SİL
    if ($islem === "sil") {
        $personel_id = intval($_POST["personel_id"] ?? 0);

        if ($personel_id <= 0) {
            $hata = "Geçersiz personel ID.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM personel WHERE personel_id = ?");
            $stmt->execute([$personel_id]);

            header("Location: " . $_SERVER["PHP_SELF"] . "?durum=silindi");
            exit;
        }
    }
}

// ===============================
// DÜZENLENECEK KAYDI GETİR
// ===============================

if (isset($_GET["duzenle"])) {
    $personel_id = intval($_GET["duzenle"]);

    if ($personel_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM personel WHERE personel_id = ?");
        $stmt->execute([$personel_id]);
        $duzenlenecek = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// ===============================
// VERİLERİ LİSTELE
// ===============================

$personeller = $pdo
    ->query("SELECT * FROM personel ORDER BY personel_id DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

$toplamKayit = count($personeller);

// ===============================
// DURUM MESAJLARI
// ===============================

if (isset($_GET["durum"])) {
    if ($_GET["durum"] === "eklendi") {
        $mesaj = "Personel başarıyla eklendi.";
    } elseif ($_GET["durum"] === "guncellendi") {
        $mesaj = "Personel başarıyla güncellendi.";
    } elseif ($_GET["durum"] === "silindi") {
        $mesaj = "Personel başarıyla silindi.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Yönetim Paneli</title>

    <link href="https://hamzan.info/lib/bootstrap/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f5f6fa;
        }

        .card {
            border: none;
            border-radius: 14px;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn {
            border-radius: 10px;
        }

        .table {
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <div class="container py-5">

        <div class="row justify-content-center">
            <div class="col-lg-10">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Personel Yönetim Paneli</h2>
                        <p class="text-muted mb-0">Personel ekleme, listeleme, düzenleme ve silme işlemleri</p>
                    </div>

                    <div class="badge bg-primary fs-6 p-3">
                        Toplam Kayıt: <?= $toplamKayit ?>
                    </div>
                </div>

                <?php if ($mesaj): ?>
                    <div class="alert alert-success">
                        <?= temizle($mesaj) ?>
                    </div>
                <?php endif; ?>

                <?php if ($hata): ?>
                    <div class="alert alert-danger">
                        <?= temizle($hata) ?>
                    </div>
                <?php endif; ?>

                <!-- FORM KISMI -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">

                        <?php if ($duzenlenecek): ?>
                            <h4 class="mb-3">Personel Düzenle</h4>
                        <?php else: ?>
                            <h4 class="mb-3">Personel Ekle</h4>
                        <?php endif; ?>

                        <form method="post">

                            <?php if ($duzenlenecek): ?>
                                <input type="hidden" name="islem" value="guncelle">
                                <input type="hidden" name="personel_id"
                                    value="<?= temizle($duzenlenecek["personel_id"]) ?>">
                            <?php else: ?>
                                <input type="hidden" name="islem" value="ekle">
                            <?php endif; ?>

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ad Soyad</label>
                                    <input type="text" name="ad_soyad" class="form-control" required
                                        value="<?= $duzenlenecek ? temizle($duzenlenecek["ad_soyad"]) : "" ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">İşe Başlama Tarihi</label>
                                    <input type="date" name="tarih" class="form-control" required
                                        value="<?= $duzenlenecek ? temizle($duzenlenecek["ise_baslama"]) : "" ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maaş</label>
                                    <input type="number" name="maas" step="0.01" min="0" class="form-control" required
                                        value="<?= $duzenlenecek ? temizle($duzenlenecek["maas"]) : "" ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Departman</label>
                                    <input type="text" name="departman" class="form-control" required
                                        value="<?= $duzenlenecek ? temizle($duzenlenecek["departman_adi"]) : "" ?>">
                                </div>

                            </div>

                            <?php if ($duzenlenecek): ?>
                                <button type="submit" class="btn btn-warning w-100 mb-2">
                                    Güncelle
                                </button>

                                <a href="<?= $_SERVER["PHP_SELF"] ?>" class="btn btn-secondary w-100">
                                    Vazgeç
                                </a>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary w-100">
                                    Kaydet
                                </button>
                            <?php endif; ?>

                        </form>
                    </div>
                </div>

                <!-- LİSTE KISMI -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">Personel Listesi</h4>

                        <?php if ($toplamKayit > 0): ?>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Ad Soyad</th>
                                            <th>İşe Başlama</th>
                                            <th>Maaş</th>
                                            <th>Departman</th>
                                            <th style="width: 180px;">İşlemler</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($personeller as $p): ?>
                                            <tr>
                                                <td><?= temizle($p["personel_id"]) ?></td>
                                                <td><?= temizle($p["ad_soyad"]) ?></td>
                                                <td><?= temizle($p["ise_baslama"]) ?></td>
                                                <td><?= number_format($p["maas"], 2, ",", ".") ?> TL</td>
                                                <td><?= temizle($p["departman_adi"]) ?></td>

                                                <td>
                                                    <a href="?duzenle=<?= temizle($p["personel_id"]) ?>"
                                                        class="btn btn-sm btn-warning">
                                                        Düzenle
                                                    </a>

                                                    <form method="post" style="display:inline-block;"
                                                        onsubmit="return confirm('Bu personeli silmek istediğine emin misin?');">
                                                        <input type="hidden" name="islem" value="sil">
                                                        <input type="hidden" name="personel_id"
                                                            value="<?= temizle($p["personel_id"]) ?>">

                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            Sil
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php else: ?>

                            <div class="alert alert-warning mb-0">
                                Henüz personel kaydı bulunmuyor.
                            </div>

                        <?php endif; ?>

                    </div>
                </div>

            </div>
        </div>

    </div>

    <script src="https://hamzan.info/lib/bootstrap/bootstrap.min.js"></script>

</body>

</html>