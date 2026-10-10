
<?php
session_start();

/* =========================================
   KEAMANAN ADMIN
========================================= */

if (empty($_SESSION["admin_login"])) {
    header("Location: admin.php");
    exit;
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$csrf = $_SESSION["csrf_token"];

/* =========================================
   KONEKSI DATABASE
========================================= */

$host = "localhost";
$dbname = "warung-warung";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Database gagal terhubung. Periksa konfigurasi MySQL.");
}

/* =========================================
   DAFTAR MENU DARI menu.html
   Harga disimpan sebagai angka rupiah.
   Untuk menu dengan rentang harga, harga
   awal menggunakan batas harga terendah.
========================================= */

$menu = [
    "Ayam Goreng Lalapan + Nasi" => 28000,
    "Ayam Penyet + Nasi" => 28000,
    "Ayam Bakar + Nasi" => 33000,
    "Soto Ayam + Nasi" => 25000,
    "Hati Ampela + Nasi" => 23000,
    "Rawon + Nasi" => 35000,
    "Ikan Lele + Nasi" => 40000,
    "Ikan Laut" => 40000,
    "Ayam Goreng Tanpa Nasi" => 23000,
    "Ayam Bakar Tanpa Nasi" => 28000,
    "Soto Ayam Tanpa Nasi" => 20000,
    "Hati Ampela Tanpa Nasi" => 18000,

    "Hati Ampela" => 10000,
    "Tahu Tempe" => 10000,
    "Terong Goreng" => 5000,
    "Kol Goreng" => 5000,
    "Nasi" => 6000,
    "Kerupuk" => 6000,
    "Lombok Bungkus" => 3000,
    "Sup" => 5000,

    "Es Jeruk Peras" => 5000,
    "Es Jeruk Maroko" => 5000,
    "Es Jeruk Nipis" => 5000,
    "Es Jeruk Manis" => 5000,
    "Es Jeruk" => 5000,
    "Es Sweet Orange" => 5000,
    "Es Milky Orange" => 5000,
    "Es Milky Peach" => 5000,
    "Es Lemon Tea" => 5000,
    "Es Apple Tea" => 5000,
    "Es Orange Tea" => 5000,
    "Es Black Currant Tea" => 5000,
    "Es Lychee Tea" => 5000,
    "Es Mango" => 5000,
    "Es Melon" => 5000,
    "Es Madu Lemon" => 5000,
    "Es Strawberry" => 5000,
    "Es Markisa" => 5000,
    "Es Nanas" => 5000,
    "Es Anggur" => 5000,

    "Es Teh / Teh Hangat" => 5000,
    "Air Mineral" => 5000,
    "Teh Pucuk / Teh Kotak" => 5000,
    "Susu Ultra Milk" => 5000,
    "Teh Botol Kaca" => 5000,
    "Teh Botol Plastik" => 8000,
    "Fruitea" => 8000,
    "Kopi Hitam" => 7000,
    "Kopi Susu" => 10000
];

/* =========================================
   FUNGSI FORMAT RUPIAH
========================================= */

function rupiah($angka)
{
    return "Rp " . number_format((float)$angka, 0, ",", ".");
}

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

/* =========================================
   HAPUS PESANAN
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_order"])
) {
    if (
        !is_string($_POST["csrf_token"] ?? null) ||
        !hash_equals($csrf, $_POST["csrf_token"])
    ) {
        http_response_code(403);
        die("Permintaan tidak valid. Muat ulang halaman.");
    }

    $id = (int)($_POST["order_id"] ?? 0);

    if ($id > 0) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "DELETE FROM order_items WHERE order_id = ?"
            );
            $stmt->execute([$id]);

            $stmt = $pdo->prepare(
                "DELETE FROM orders WHERE id = ?"
            );
            $stmt->execute([$id]);

            $pdo->commit();

            header("Location: admin.php?order_deleted=1");
            exit;
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            die("Gagal menghapus pesanan.");
        }
    }
}

/* =========================================
   SIMPAN PERUBAHAN PESANAN
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["save_order"])
) {
    if (
        !is_string($_POST["csrf_token"] ?? null) ||
        !hash_equals($csrf, $_POST["csrf_token"])
    ) {
        http_response_code(403);
        die("Permintaan tidak valid. Muat ulang halaman.");
    }

    $id = (int)($_POST["order_id"] ?? 0);
    $nama = trim($_POST["nama"] ?? "");
    $catatan = trim($_POST["catatan"] ?? "");
    $status = $_POST["status"] ?? "baru";

    $statusValid = [
        "baru",
        "diproses",
        "selesai",
        "dibatalkan"
    ];

    if (
        $id < 1 ||
        $nama === "" ||
        !in_array($status, $statusValid, true)
    ) {
        die("Data pesanan tidak valid.");
    }

    $namaMenu = $_POST["nama_menu"] ?? [];
    $jumlahMenu = $_POST["jumlah"] ?? [];

    if (!is_array($namaMenu) || !is_array($jumlahMenu)) {
        die("Daftar menu tidak valid.");
    }

    $itemsBaru = [];

    foreach ($namaMenu as $key => $namaItem) {
        $namaItem = trim((string)$namaItem);
        $jumlah = filter_var(
            $jumlahMenu[$key] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!isset($menu[$namaItem])) {
            die("Menu tidak tersedia di daftar menu.");
        }

        if ($jumlah === false || $jumlah < 1 || $jumlah > 999) {
            die("Jumlah menu harus antara 1 dan 999.");
        }

        $itemsBaru[] = [
            "nama" => $namaItem,
            "harga" => $menu[$namaItem],
            "jumlah" => $jumlah
        ];
    }

    if (!$itemsBaru) {
        die("Pesanan harus memiliki minimal satu menu.");
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "UPDATE orders
             SET nama = ?, catatan = ?, status = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $nama,
            $catatan,
            $status,
            $id
        ]);

        /* Ganti seluruh detail menu dengan pilihan terbaru. */
        $stmt = $pdo->prepare(
            "DELETE FROM order_items WHERE order_id = ?"
        );
        $stmt->execute([$id]);

        $stmtInsert = $pdo->prepare(
            "INSERT INTO order_items
                (order_id, nama_menu, harga, jumlah)
             VALUES (?, ?, ?, ?)"
        );

        foreach ($itemsBaru as $item) {
            $stmtInsert->execute([
                $id,
                $item["nama"],
                $item["harga"],
                $item["jumlah"]
            ]);
        }

        $pdo->commit();

        header("Location: kelola-pesanan.php?edit=" . $id . "&saved=1");
        exit;
    } catch (PDOException $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        die("Gagal menyimpan perubahan. Periksa struktur tabel database.");
    }
}

/* =========================================
   TAMPILAN EDIT SATU PESANAN
========================================= */

$editId = (int)($_GET["edit"] ?? 0);
$order = null;

if ($editId > 0) {
    $stmt = $pdo->prepare(
        "SELECT id, tanggal, nama, catatan, status
         FROM orders
         WHERE id = ?"
    );
    $stmt->execute([$editId]);
    $order = $stmt->fetch();

    if (!$order) {
        die("Pesanan tidak ditemukan.");
    }

    $stmt = $pdo->prepare(
        "SELECT id, nama_menu, harga, jumlah
         FROM order_items
         WHERE order_id = ?
         ORDER BY id ASC"
    );
    $stmt->execute([$editId]);
    $order["items"] = $stmt->fetchAll();
}

/* =========================================
   DAFTAR PESANAN JIKA TIDAK SEDANG EDIT
========================================= */

$orders = [];

if ($editId === 0) {
    $orders = $pdo->query(
        "SELECT id, tanggal, nama, catatan, status
         FROM orders
         ORDER BY id DESC"
    )->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Pesanan - Warung Mas Gondrong</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-zinc-950 text-white">

<header class="border-b border-orange-500 bg-black">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 p-6">
        <div>
            <p class="font-bold uppercase tracking-widest text-orange-500">
                Admin
            </p>
            <h1 class="text-3xl font-black">Kelola Pesanan</h1>
            <p class="mt-2 text-zinc-400">Warung Sari Laut Mas Gondrong</p>
        </div>

        <div class="flex gap-3">
            <a href="admin.php"
               class="rounded-lg bg-zinc-800 px-4 py-3 font-bold">
                ← Dashboard
            </a>

            <a href="admin.php?logout=1"
               class="rounded-lg bg-red-600 px-4 py-3 font-bold">
                Logout
            </a>
        </div>
    </div>
</header>

<main class="mx-auto max-w-6xl space-y-6 p-6">

<?php if ($editId > 0 && $order): ?>

    <?php if (isset($_GET["saved"])): ?>
        <div class="rounded-xl border border-green-600 bg-green-900/40 p-4 text-green-200">
            Perubahan pesanan berhasil disimpan.
        </div>
    <?php endif; ?>

    <section class="rounded-2xl border border-orange-500 bg-zinc-900 p-6">

        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-orange-500">
                    Edit Pesanan #<?= (int)$order["id"] ?>
                </h2>
                <p class="mt-2 text-zinc-400">
                    <?= e($order["tanggal"]) ?>
                </p>
            </div>

            <a href="admin.php"
               class="rounded-lg bg-zinc-800 px-4 py-2 font-bold">
                Batal
            </a>
        </div>

        <form method="POST" id="editForm" class="space-y-6">

            <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
            <input type="hidden" name="order_id" value="<?= (int)$order["id"] ?>">

            <div>
                <label class="mb-2 block font-bold">Nama Pelanggan</label>
                <input
                    type="text"
                    name="nama"
                    required
                    value="<?= e($order["nama"]) ?>"
                    class="w-full rounded-xl border border-zinc-700 bg-black p-3 focus:border-orange-500 focus:outline-none"
                >
            </div>

            <div>
                <label class="mb-2 block font-bold">Status Pesanan</label>
                <select
                    name="status"
                    class="w-full rounded-xl border border-zinc-700 bg-black p-3 focus:border-orange-500 focus:outline-none"
                >
                    <?php
                    $statusOptions = [
                        "baru" => "Baru",
                        "diproses" => "Diproses",
                        "selesai" => "Selesai",
                        "dibatalkan" => "Dibatalkan"
                    ];
                    foreach ($statusOptions as $value => $label):
                    ?>
                        <option
                            value="<?= e($value) ?>"
                            <?= $order["status"] === $value ? "selected" : "" ?>
                        >
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="mb-2 block font-bold">Catatan Pesanan</label>
                <textarea
                    name="catatan"
                    rows="3"
                    class="w-full rounded-xl border border-zinc-700 bg-black p-3 focus:border-orange-500 focus:outline-none"
                ><?= e($order["catatan"] ?? "") ?></textarea>
            </div>

            <div class="border-t border-zinc-700 pt-6">

                <h3 class="text-2xl font-black text-orange-500">
                    Menu Pesanan
                </h3>

                <p class="mt-2 text-zinc-400">
                    Pilih menu dari daftar. Harga akan berubah otomatis.
                </p>

                <div id="itemContainer" class="mt-5 space-y-4">

                    <?php foreach ($order["items"] as $item): ?>
                        <div class="item-row rounded-xl border border-zinc-800 bg-black p-4">

                            <div class="mb-4 flex items-center justify-between gap-3">
                                <p class="font-bold text-orange-500">Menu Pesanan</p>
                                <button
                                    type="button"
                                    class="remove-item rounded-lg bg-red-600 px-3 py-2 text-sm font-bold hover:bg-red-500"
                                >
                                    Hapus Menu
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_150px]">

                                <div>
                                    <label class="mb-2 block font-bold">
                                        Pilih Nama Menu
                                    </label>

                                    <select
                                        name="nama_menu[]"
                                        required
                                        class="menu-select w-full rounded-lg border border-zinc-700 bg-zinc-900 p-3 focus:border-orange-500 focus:outline-none"
                                    >
                                        <?php foreach ($menu as $namaMenuPilihan => $hargaMenu): ?>
                                            <option
                                                value="<?= e($namaMenuPilihan) ?>"
                                                data-price="<?= (int)$hargaMenu ?>"
                                                <?= $item["nama_menu"] === $namaMenuPilihan ? "selected" : "" ?>
                                            >
                                                <?= e($namaMenuPilihan) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <p class="mt-3 text-zinc-400">
                                        Harga satuan:
                                        <strong class="item-price text-lg text-orange-500">
                                            <?= rupiah($menu[$item["nama_menu"]] ?? $item["harga"]) ?>
                                        </strong>
                                    </p>
                                </div>

                                <div>
                                    <label class="mb-2 block font-bold">Jumlah</label>
                                    <input
                                        type="number"
                                        name="jumlah[]"
                                        min="1"
                                        max="999"
                                        required
                                        value="<?= max(1, (int)$item["jumlah"]) ?>"
                                        class="w-full rounded-lg border border-zinc-700 bg-zinc-900 p-3 text-center focus:border-orange-500 focus:outline-none"
                                    >
                                </div>

                            </div>

                        </div>
                    <?php endforeach; ?>

                </div>

                <button
                    type="button"
                    id="addItem"
                    class="mt-4 rounded-xl border border-orange-500 px-5 py-3 font-bold text-orange-500 hover:bg-orange-500 hover:text-black"
                >
                    + Tambah Menu
                </button>

            </div>

            <div class="flex flex-col gap-3 border-t border-zinc-700 pt-6 sm:flex-row">

                <button
                    type="submit"
                    name="save_order"
                    class="flex-1 rounded-xl bg-orange-500 px-5 py-4 font-black text-black hover:bg-orange-400"
                >
                    💾 Simpan Perubahan
                </button>

                <a
                    href="admin.php"
                    class="rounded-xl bg-zinc-800 px-6 py-4 text-center font-bold hover:bg-zinc-700"
                >
                    Batal
                </a>

            </div>

        </form>
    </section>

<?php else: ?>

    <section class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
        <p class="text-zinc-400">Total Pesanan</p>
        <p class="mt-2 text-4xl font-black text-orange-500">
            <?= count($orders) ?>
        </p>
    </section>

    <section>
        <h2 class="mb-4 text-2xl font-black">Daftar Pesanan</h2>

        <div class="overflow-x-auto rounded-xl border border-zinc-800">
            <table class="w-full text-left">
                <thead class="bg-zinc-800">
                    <tr>
                        <th class="p-3">ID</th>
                        <th class="p-3">Tanggal</th>
                        <th class="p-3">Nama Pelanggan</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-right">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($orders as $row): ?>
                        <tr class="border-t border-zinc-800">
                            <td class="p-3">#<?= (int)$row["id"] ?></td>
                            <td class="whitespace-nowrap p-3"><?= e($row["tanggal"]) ?></td>
                            <td class="p-3 font-bold"><?= e($row["nama"]) ?></td>
                            <td class="p-3"><?= e($row["status"]) ?></td>
                            <td class="p-3">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="kelola-pesanan.php?edit=<?= (int)$row["id"] ?>"
                                        class="rounded-lg bg-blue-600 px-4 py-2 font-bold hover:bg-blue-500"
                                    >
                                        ✏️ Edit
                                    </a>

                                    <form method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus pesanan ini beserta semua detail menunya?')">
                                        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
                                        <input type="hidden" name="order_id" value="<?= (int)$row["id"] ?>">

                                        <button
                                            type="submit"
                                            name="delete_order"
                                            class="rounded-lg bg-red-600 px-4 py-2 font-bold hover:bg-red-500"
                                        >
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$orders): ?>
                        <tr>
                            <td colspan="5" class="p-5 text-center text-zinc-400">
                                Belum ada pesanan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php endif; ?>

</main>

<footer class="mt-10 border-t border-zinc-800 py-8 text-center text-zinc-500">
    © 2026 Warung Sari Laut Mas Gondrong
</footer>

<script>
const menuHarga = <?= json_encode(
    $menu,
    JSON_UNESCAPED_UNICODE |
    JSON_HEX_TAG |
    JSON_HEX_APOS |
    JSON_HEX_QUOT |
    JSON_HEX_AMP
) ?>;

function formatRupiah(angka) {
    return "Rp " + Number(angka).toLocaleString("id-ID");
}

function perbaruiHarga(baris) {
    const select = baris.querySelector(".menu-select");
    const harga = baris.querySelector(".item-price");

    if (!select || !harga) return;

    const nama = select.value;
    harga.textContent = formatRupiah(menuHarga[nama] || 0);
}

function buatPilihanMenu(namaTerpilih = "", jumlah = 1) {
    const baris = document.createElement("div");
    baris.className = "item-row rounded-xl border border-zinc-800 bg-black p-4";

    const header = document.createElement("div");
    header.className = "mb-4 flex items-center justify-between gap-3";

    const judul = document.createElement("p");
    judul.className = "font-bold text-orange-500";
    judul.textContent = "Menu Pesanan";

    const hapus = document.createElement("button");
    hapus.type = "button";
    hapus.className = "remove-item rounded-lg bg-red-600 px-3 py-2 text-sm font-bold hover:bg-red-500";
    hapus.textContent = "Hapus Menu";

    header.append(judul, hapus);

    const grid = document.createElement("div");
    grid.className = "grid gap-4 md:grid-cols-[minmax(0,1fr)_150px]";

    const kolomMenu = document.createElement("div");

    const labelMenu = document.createElement("label");
    labelMenu.className = "mb-2 block font-bold";
    labelMenu.textContent = "Pilih Nama Menu";

    const select = document.createElement("select");
    select.name = "nama_menu[]";
    select.required = true;
    select.className = "menu-select w-full rounded-lg border border-zinc-700 bg-zinc-900 p-3 focus:border-orange-500 focus:outline-none";

    Object.keys(menuHarga).forEach(nama => {
        const option = document.createElement("option");
        option.value = nama;
        option.textContent = nama;
        option.dataset.price = menuHarga[nama];

        if (nama === namaTerpilih) {
            option.selected = true;
        }

        select.appendChild(option);
    });

    if (!namaTerpilih) {
        select.selectedIndex = 0;
    }

    const teksHarga = document.createElement("p");
    teksHarga.className = "mt-3 text-zinc-400";
    teksHarga.append("Harga satuan: ");

    const harga = document.createElement("strong");
    harga.className = "item-price text-lg text-orange-500";
    teksHarga.appendChild(harga);

    kolomMenu.append(labelMenu, select, teksHarga);

    const kolomJumlah = document.createElement("div");

    const labelJumlah = document.createElement("label");
    labelJumlah.className = "mb-2 block font-bold";
    labelJumlah.textContent = "Jumlah";

    const inputJumlah = document.createElement("input");
    inputJumlah.type = "number";
    inputJumlah.name = "jumlah[]";
    inputJumlah.min = "1";
    inputJumlah.max = "999";
    inputJumlah.required = true;
    inputJumlah.value = Math.max(1, Number(jumlah) || 1);
    inputJumlah.className = "w-full rounded-lg border border-zinc-700 bg-zinc-900 p-3 text-center focus:border-orange-500 focus:outline-none";

    kolomJumlah.append(labelJumlah, inputJumlah);
    grid.append(kolomMenu, kolomJumlah);
    baris.append(header, grid);

    select.addEventListener("change", () => perbaruiHarga(baris));
    hapus.addEventListener("click", () => {
        const semuaBaris = document.querySelectorAll(".item-row");

        if (semuaBaris.length <= 1) {
            alert("Pesanan harus memiliki minimal satu menu.");
            return;
        }

        baris.remove();
    });

    perbaruiHarga(baris);
    return baris;
}

const container = document.getElementById("itemContainer");

if (container) {
    container.querySelectorAll(".item-row").forEach(baris => {
        const select = baris.querySelector(".menu-select");
        const tombolHapus = baris.querySelector(".remove-item");

        select.addEventListener("change", () => perbaruiHarga(baris));

        tombolHapus.addEventListener("click", () => {
            if (container.querySelectorAll(".item-row").length <= 1) {
                alert("Pesanan harus memiliki minimal satu menu.");
                return;
            }

            baris.remove();
        });

        perbaruiHarga(baris);
    });

    document.getElementById("addItem").addEventListener("click", () => {
        container.appendChild(buatPilihanMenu());
    });
}
</script>

</body>
</html>
