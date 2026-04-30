<?php

$host = "localhost";
$username = "root";
$password = "";
$dbname = "sinav";
$charset = "utf8mb4";
$collate = "utf8mb4_general_ci";



/*
NEDEN ILK OLARAK MYSQL BAGLANTISI KURULUR?

*/
try {
    //once mysql baglantisi kurulur
    $pdo = new PDO("mysql:host=$host;", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //veritabanı oluşturulur
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET $charset COLLATE $collate");

    //veritabanına bağlanılır
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //tablo oluşturulur
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
    echo "Hata oluştu: " . $e->getMessage();
}

//form gönderildiğinde verileri al ve veritabanına ekle
if ($_POST) {
    $stmt = $pdo->prepare(
        "INSERT INTO personel (ad_soyad, ise_baslama, maas, departman_adi) VALUES (?, ?, ?, ?)"
    );

    //form verilerini al
    $stmt->execute([
        $_POST['ad_soyad'],
        $_POST['tarih'],
        $_POST['maas'],
        $_POST['departman']
    ]);
}
//verileri çek ve ekrana yazdır
//query() SELECT sorgusu için kullanılır
$personeller = $pdo->query("SELECT *FROM personel")->fetchAll(PDO::FETCH_ASSOC);
//fetchAll(): tüm kayıtları dizi olarak getirir
?>

<!-- HTML KISMI -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
    <div class="container w-100 d-flex flex-column align-items-center mt-5">
        <h2>Personel Ekle</h2>
        <form method="post" class="mb-5 w-100 d-flex flex-column align-items-center">
            <label for="adsoyad">Ad Soyad:</label>
            <input type="text" name="ad_soyad" required> <br>
            <label for="tarih">İşe Başlama:</label>
            <input type="date" name="tarih" required> <br>
            <label for="maas">Maaş:</label>
            <input type="number" name="maas" step="0.1" required> <br>
            <label for="departman">Departman:</label>
            <input type="text" name="departman" required> <br>
            <button type="submit">Kaydet</button>
        </form>
        <h2>Personel Listesi</h2>
        <table class="table table-striped">
            <tr>
                <th>ID</th>
                <th>Ad Soyad</th>
                <th>İşe Başlama Tarihi</th>
                <th>Maaş</th>
                <th>Departman</th>
            </tr>
            <?php foreach ($personeller as $p): ?>
                <tr>
                    <td><?= $p['personel_id'] ?></td>
                    <td><?= $p['ad_soyad'] ?></td>
                    <td><?= $p['ise_baslama'] ?></td>
                    <td><?= $p['maas'] ?></td>
                    <td><?= $p['departman_adi'] ?></td>
                </tr>

            <?php endforeach; ?>

        </table>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>