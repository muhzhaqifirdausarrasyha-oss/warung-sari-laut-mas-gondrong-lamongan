<?php

session_start();

header("Content-Type: application/json; charset=UTF-8");


/*
========================================
KONEKSI DATABASE
========================================
*/

$host = "localhost";
$dbname = "warung-warung";
$username = "root";
$password = "";


try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $e) {

    response(
        "error",
        "Database gagal terhubung."
    );

}


/*
========================================
FILE CONFIG
========================================
*/

$configFile =
    __DIR__ . "/data/config.json";


/*
========================================
FUNGSI BACA JSON
========================================
*/

function bacaJSON($file)
{

    if (!file_exists($file)) {

        return [];

    }


    $isi =
        file_get_contents($file);


    if (
        $isi === false ||
        trim($isi) === ""
    ) {

        return [];

    }


    $data =
        json_decode(
            $isi,
            true
        );


    if (!is_array($data)) {

        return [];

    }


    return $data;

}


/*
========================================
FUNGSI RESPONSE
========================================
*/

function response(
    $status,
    $message,
    $data = null
) {

    echo json_encode(
        [
            "status" => $status,
            "message" => $message,
            "data" => $data
        ],
        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE
    );

    exit;

}


/*
========================================
CEK LOGIN ADMIN
========================================
*/

function cekAdmin()
{

    if (
        !isset($_SESSION["admin_login"]) ||
        $_SESSION["admin_login"] !== true
    ) {

        response(
            "error",
            "Akses admin diperlukan."
        );

    }

}


/*
========================================
AMBIL ACTION
========================================
*/

$action =
    $_GET["action"] ?? "";


/*
========================================
AMBIL CONFIG
========================================
*/

if ($action === "config") {

    $config =
        bacaJSON(
            $configFile
        );


    response(
        "success",
        "Konfigurasi berhasil diambil.",
        $config
    );

}


/*
========================================
SIMPAN PESANAN
========================================
*/

if ($action === "order") {

    if (
        $_SERVER["REQUEST_METHOD"]
        !== "POST"
    ) {

        response(
            "error",
            "Method harus POST."
        );

    }


    /*
    ------------------------------------
    AMBIL DATA
    ------------------------------------
    */

    $nama =
        trim(
            $_POST["nama"] ?? ""
        );


    $pesananJSON =
        $_POST["pesanan"] ?? "";


    $catatan =
        trim(
            $_POST["catatan"] ?? ""
        );


    /*
    ------------------------------------
    VALIDASI NAMA
    ------------------------------------
    */

    if ($nama === "") {

        response(
            "error",
            "Nama pelanggan wajib diisi."
        );

    }


    /*
    ------------------------------------
    UBAH PESANAN JSON
    ------------------------------------
    */

    $pesanan =
        json_decode(
            $pesananJSON,
            true
        );


    if (
        !is_array($pesanan) ||
        count($pesanan) === 0
    ) {

        response(
            "error",
            "Pesanan belum dipilih."
        );

    }


    /*
    ------------------------------------
    MULAI TRANSAKSI
    ------------------------------------
    */

    try {

        $pdo->beginTransaction();


        /*
        --------------------------------
        SIMPAN DATA ORDER
        --------------------------------
        */

        $stmt =
            $pdo->prepare(
                "INSERT INTO orders
                (nama, catatan, status)
                VALUES (?, ?, ?)"
            );


        $stmt->execute(
            [
                $nama,
                $catatan,
                "baru"
            ]
        );


        /*
        --------------------------------
        AMBIL ID ORDER
        --------------------------------
        */

        $orderID =
            $pdo->lastInsertId();


        /*
        --------------------------------
        SIMPAN DETAIL ORDER
        --------------------------------
        */

        $stmtItem =
            $pdo->prepare(
                "INSERT INTO order_items
                (
                    order_id,
                    nama_menu,
                    harga,
                    jumlah
                )
                VALUES (?, ?, ?, ?)"
            );


        foreach (
            $pesanan
            as $item
        ) {

            $namaMenu =
                $item["nama"] ?? "";


            $harga =
                $item["harga"] ?? "";


            $jumlah =
                intval(
                    $item["jumlah"] ?? 1
                );


            $stmtItem->execute(
                [
                    $orderID,
                    $namaMenu,
                    $harga,
                    $jumlah
                ]
            );

        }


        /*
        --------------------------------
        COMMIT
        --------------------------------
        */

        $pdo->commit();


    } catch (PDOException $e) {

        if (
            $pdo->inTransaction()
        ) {

            $pdo->rollBack();

        }


        response(
            "error",
            "Pesanan gagal disimpan ke database."
        );

    }


    /*
    ====================================
    AMBIL NOMOR WHATSAPP
    ====================================
    */

    $config =
        bacaJSON(
            $configFile
        );


    $nomorWA =
        $config["kontak"]["whatsapp"]
        ?? "";


    /*
    ====================================
    BUAT PESAN WHATSAPP
    ====================================
    */

    $pesanWA =
        "Halo Mas Gondrong, saya mau pesan."
        . "\n\n";


    $pesanWA .=
        "Nama: " .
        $nama .
        "\n\n";


    $pesanWA .=
        "Pesanan:\n";


    foreach (
        $pesanan
        as $index => $item
    ) {

        $pesanWA .=
            ($index + 1) .
            ". " .
            ($item["nama"] ?? "") .
            " × " .
            ($item["jumlah"] ?? 1) .
            "\n";

    }


    if ($catatan !== "") {

        $pesanWA .=
            "\nCatatan:\n" .
            $catatan .
            "\n";

    }


    $pesanWA .=
        "\nTerima kasih.";


    /*
    ====================================
    URL WHATSAPP
    ====================================
    */

    $urlWhatsApp =
        "https://wa.me/" .
        $nomorWA .
        "?text=" .
        urlencode(
            $pesanWA
        );


    /*
    ====================================
    RESPONSE
    ====================================
    */

    response(
        "success",
        "Pesanan berhasil disimpan.",
        [
            "id" =>
                $orderID,

            "whatsapp" =>
                $urlWhatsApp
        ]
    );

}


/*
========================================
SIMPAN PESAN KONTAK
========================================
*/

if ($action === "message") {

    if (
        $_SERVER["REQUEST_METHOD"]
        !== "POST"
    ) {

        response(
            "error",
            "Method harus POST."
        );

    }


    $nama =
        trim(
            $_POST["nama"] ?? ""
        );


    $pesan =
        trim(
            $_POST["pesan"] ?? ""
        );


    if ($nama === "") {

        response(
            "error",
            "Nama wajib diisi."
        );

    }


    if ($pesan === "") {

        response(
            "error",
            "Pesan wajib diisi."
        );

    }


    try {

        $stmt =
            $pdo->prepare(
                "INSERT INTO messages
                (nama, pesan)
                VALUES (?, ?)"
            );


        $stmt->execute(
            [
                $nama,
                $pesan
            ]
        );


    } catch (PDOException $e) {

        response(
            "error",
            "Pesan gagal disimpan ke database."
        );

    }


    response(
        "success",
        "Pesan berhasil disimpan."
    );

}


/*
========================================
AMBIL DATA PESANAN
========================================
*/

if ($action === "orders") {

    cekAdmin();


    try {

        $stmt =
            $pdo->query(
                "SELECT
                    o.id,
                    o.tanggal,
                    o.nama,
                    o.catatan,
                    o.status,
                    oi.nama_menu,
                    oi.harga,
                    oi.jumlah
                FROM orders o
                LEFT JOIN order_items oi
                    ON o.id = oi.order_id
                ORDER BY o.id DESC"
            );


        $data =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        response(
            "success",
            "Data pesanan berhasil diambil.",
            $data
        );


    } catch (PDOException $e) {

        response(
            "error",
            "Gagal mengambil data pesanan."
        );

    }

}


/*
========================================
AMBIL DATA PESAN
========================================
*/

if ($action === "messages") {

    cekAdmin();


    try {

        $stmt =
            $pdo->query(
                "SELECT
                    id,
                    tanggal,
                    nama,
                    pesan
                FROM messages
                ORDER BY id DESC"
            );


        $data =
            $stmt->fetchAll(
                PDO::FETCH_ASSOC
            );


        response(
            "success",
            "Data pesan berhasil diambil.",
            $data
        );


    } catch (PDOException $e) {

        response(
            "error",
            "Gagal mengambil data pesan."
        );

    }

}


/*
========================================
UPDATE STATUS PESANAN
========================================
*/

if ($action === "update_order") {

    cekAdmin();


    if (
        $_SERVER["REQUEST_METHOD"]
        !== "POST"
    ) {

        response(
            "error",
            "Method harus POST."
        );

    }


    $id =
        intval(
            $_POST["id"] ?? 0
        );


    $status =
        trim(
            $_POST["status"] ?? ""
        );


    if ($id <= 0) {

        response(
            "error",
            "ID pesanan tidak valid."
        );

    }


    /*
    ------------------------------------
    STATUS YANG DIPERBOLEHKAN
    ------------------------------------
    */

    $statusValid =
        [
            "baru",
            "diproses",
            "selesai",
            "dibatalkan"
        ];


    if (
        !in_array(
            $status,
            $statusValid,
            true
        )
    ) {

        response(
            "error",
            "Status tidak valid."
        );

    }


    try {

        $stmt =
            $pdo->prepare(
                "UPDATE orders
                 SET status = ?
                 WHERE id = ?"
            );


        $stmt->execute(
            [
                $status,
                $id
            ]
        );


        if (
            $stmt->rowCount() === 0
        ) {

            response(
                "error",
                "Pesanan tidak ditemukan."
            );

        }


        response(
            "success",
            "Status pesanan berhasil diperbarui."
        );


    } catch (PDOException $e) {

        response(
            "error",
            "Status pesanan gagal diperbarui."
        );

    }

}


/*
========================================
HAPUS PESANAN
========================================
*/

if ($action === "delete_order") {

    cekAdmin();


    if (
        $_SERVER["REQUEST_METHOD"]
        !== "POST"
    ) {

        response(
            "error",
            "Method harus POST."
        );

    }


    $id =
        intval(
            $_POST["id"] ?? 0
        );


    if ($id <= 0) {

        response(
            "error",
            "ID pesanan tidak valid."
        );

    }


    try {

        $pdo->beginTransaction();


        /*
        --------------------------------
        HAPUS DETAIL ORDER
        --------------------------------
        */

        $stmtItem =
            $pdo->prepare(
                "DELETE FROM order_items
                 WHERE order_id = ?"
            );


        $stmtItem->execute(
            [
                $id
            ]
        );


        /*
        --------------------------------
        HAPUS ORDER
        --------------------------------
        */

        $stmt =
            $pdo->prepare(
                "DELETE FROM orders
                 WHERE id = ?"
            );


        $stmt->execute(
            [
                $id
            ]
        );


        if (
            $stmt->rowCount() === 0
        ) {

            $pdo->rollBack();


            response(
                "error",
                "Pesanan tidak ditemukan."
            );

        }


        $pdo->commit();


        response(
            "success",
            "Pesanan berhasil dihapus."
        );


    } catch (PDOException $e) {

        if (
            $pdo->inTransaction()
        ) {

            $pdo->rollBack();

        }


        response(
            "error",
            "Pesanan gagal dihapus."
        );

    }

}


/*
========================================
HAPUS PESAN PELANGGAN
========================================
*/

if ($action === "delete_message") {

    cekAdmin();


    if (
        $_SERVER["REQUEST_METHOD"]
        !== "POST"
    ) {

        response(
            "error",
            "Method harus POST."
        );

    }


    $id =
        intval(
            $_POST["id"] ?? 0
        );


    if ($id <= 0) {

        response(
            "error",
            "ID pesan tidak valid."
        );

    }


    try {

        $stmt =
            $pdo->prepare(
                "DELETE FROM messages
                 WHERE id = ?"
            );


        $stmt->execute(
            [
                $id
            ]
        );


        if (
            $stmt->rowCount() === 0
        ) {

            response(
                "error",
                "Pesan tidak ditemukan."
            );

        }


        response(
            "success",
            "Pesan berhasil dihapus."
        );


    } catch (PDOException $e) {

        response(
            "error",
            "Pesan gagal dihapus."
        );

    }

}


/*
========================================
ACTION TIDAK DITEMUKAN
========================================
*/

response(
    "error",
    "Action tidak ditemukan."
);

?>