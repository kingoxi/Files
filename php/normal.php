<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sinav";
$charset = "utf8mb4";

try {
    // 1. Önce MySQL sunucusuna bağlan
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Veritabanı yoksa oluştur
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    // 3. Veritabanına bağlan
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass);
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

// 5. Form gönderildiyse veriyi ekle
if ($_POST) {
    $stmt = $pdo->prepare("
        INSERT INTO personel (ad_soyad, ise_baslama, maas, departman_adi)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST["ad_soyad"],
        $_POST["tarih"],
        $_POST["maas"],
        $_POST["departman"]
    ]);
}

// 6. Verileri çek
$personeller = $pdo->query("SELECT * FROM personel")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Personel Ekle</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        input {
            margin-bottom: 10px;
            padding: 6px;
            width: 250px;
        }

        button {
            padding: 7px 15px;
            cursor: pointer;
        }

        table {
            margin-top: 25px;
            border-collapse: collapse;
            width: 700px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>

<body>

    <h2>Personel Ekle</h2>

    <form method="post">
        <label>Ad Soyad:</label><br>
        <input type="text" name="ad_soyad" required><br>

        <label>İşe Başlama Tarihi:</label><br>
        <input type="date" name="tarih" required><br>

        <label>Maaş:</label><br>
        <input type="number" step="0.01" name="maas" required><br>

        <label>Departman:</label><br>
        <input type="text" name="departman" required><br>

        <button type="submit">Kaydet</button>
    </form>

    <h2>Personel Listesi</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Ad Soyad</th>
            <th>İşe Başlama</th>
            <th>Maaş</th>
            <th>Departman</th>
        </tr>

        <?php foreach ($personeller as $p): ?>
            <tr>
                <td><?= $p["personel_id"] ?></td>
                <td><?= $p["ad_soyad"] ?></td>
                <td><?= $p["ise_baslama"] ?></td>
                <td><?= $p["maas"] ?></td>
                <td><?= $p["departman_adi"] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

</body>

</html>