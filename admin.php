<?php
session_start();

/* KONEKSI DATABASE */
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

/* LOGIN ADMIN */
$adminUsername = "admin";
$adminPassword = "admin123";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$error = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["login"])
) {
    $inputUsername = trim($_POST["username"] ?? "");
    $inputPassword = $_POST["password"] ?? "";

    if (
        hash_equals($adminUsername, $inputUsername) &&
        hash_equals($adminPassword, $inputPassword)
    ) {
        session_regenerate_id(true);
        $_SESSION["admin_login"] = true;

        header("Location: admin.php");
        exit;
    }

    $error = "Username atau password salah.";
}

/* LOGOUT */
if (isset($_GET["logout"])) {
    $_SESSION = [];
    session_destroy();

    header("Location: admin.php");
    exit;
}

/* PROSES DATA ADMIN */
if (
    !empty($_SESSION["admin_login"]) &&
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    !isset($_POST["login"])
) {
    $token = $_POST["csrf_token"] ?? "";

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["csrf_token"], $token)
    ) {
        http_response_code(403);
        die("Permintaan tidak valid. Muat ulang halaman.");
    }

    /* EDIT PESAN PELANGGAN */
    if (isset($_POST["update_message"])) {
        $messageId = (int)($_POST["message_id"] ?? 0);
        $nama = trim($_POST["nama"] ?? "");
        $pesan = trim($_POST["pesan"] ?? "");

        if ($messageId < 1 || $nama === "" || $pesan === "") {
            header("Location: admin.php?edit_error=1");
            exit;
        }

        $stmt = $pdo->prepare(
            "UPDATE messages
             SET nama = ?, pesan = ?
             WHERE id = ?"
        );

        $stmt->execute([$nama, $pesan, $messageId]);

        header("Location: admin.php?message_updated=1");
        exit;
    }

    /* HAPUS PESAN PELANGGAN */
    if (isset($_POST["delete_message"])) {
        $messageId = (int)($_POST["message_id"] ?? 0);

        if ($messageId > 0) {
            $stmt = $pdo->prepare(
                "DELETE FROM messages WHERE id = ?"
            );

            $stmt->execute([$messageId]);
        }

        header("Location: admin.php?message_deleted=1");
        exit;
    }

    /* HAPUS PESANAN */
    if (isset($_POST["delete_order"])) {
        $orderId = (int)($_POST["order_id"] ?? 0);

        if ($orderId > 0) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "DELETE FROM order_items WHERE order_id = ?"
                );
                $stmt->execute([$orderId]);

                $stmt = $pdo->prepare(
                    "DELETE FROM orders WHERE id = ?"
                );
                $stmt->execute([$orderId]);

                $pdo->commit();
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                die("Gagal menghapus pesanan.");
            }
        }

        header("Location: admin.php?order_deleted=1");
        exit;
    }
}

/* AMBIL DATA DASHBOARD */
$orders = [];
$messages = [];
$totalOrders = 0;
$totalMessages = 0;
$editMessage = null;

if (!empty($_SESSION["admin_login"])) {
    $totalOrders = (int)$pdo->query(
        "SELECT COUNT(*) FROM orders"
    )->fetchColumn();

    $totalMessages = (int)$pdo->query(
        "SELECT COUNT(*) FROM messages"
    )->fetchColumn();

    $orders = $pdo->query(
        "SELECT id, tanggal, nama, catatan, status
         FROM orders
         ORDER BY id DESC"
    )->fetchAll();

    $messages = $pdo->query(
        "SELECT id, tanggal, nama, pesan
         FROM messages
         ORDER BY id DESC"
    )->fetchAll();

    $editMessageId = (int)($_GET["edit_message"] ?? 0);

    if ($editMessageId > 0) {
        $stmt = $pdo->prepare(
            "SELECT id, tanggal, nama, pesan
             FROM messages
             WHERE id = ?"
        );

        $stmt->execute([$editMessageId]);
        $editMessage = $stmt->fetch();

        if (!$editMessage) {
            $editMessageId = 0;
        }
    }
}

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Warung Mas Gondrong</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-zinc-950 text-white">

<?php if (empty($_SESSION["admin_login"])): ?>

    <!-- LOGIN ADMIN -->
    <main class="flex min-h-screen items-center justify-center p-5">
        <form
            method="POST"
            class="w-full max-w-md rounded-2xl border border-orange-500 bg-zinc-900 p-8"
        >
            <h1 class="mb-2 text-3xl font-black text-orange-500">
                Admin Login
            </h1>

            <p class="mb-6 text-zinc-400">
                Warung Sari Laut Mas Gondrong
            </p>

            <?php if ($error !== ""): ?>
                <p class="mb-4 rounded-lg bg-red-900 p-3 text-red-200">
                    <?= e($error) ?>
                </p>
            <?php endif; ?>

            <label class="mb-2 block">Username</label>
            <input
                type="text"
                name="username"
                required
                autocomplete="username"
                class="mb-4 w-full rounded-lg border border-zinc-700 bg-black p-3"
            >

            <label class="mb-2 block">Password</label>
            <input
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="mb-6 w-full rounded-lg border border-zinc-700 bg-black p-3"
            >

            <button
                type="submit"
                name="login"
                class="w-full rounded-lg bg-orange-500 p-3 font-bold text-black"
            >
                Masuk
            </button>

            <p class="mt-4 text-sm text-zinc-400">
                Akun awal: admin / admin123
            </p>
        </form>
    </main>

<?php else: ?>

    <!-- HEADER -->
    <header class="border-b border-orange-500 bg-black">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 p-6">

            <div>
                <p class="font-bold uppercase tracking-widest text-orange-500">
                    Dashboard
                </p>

                <h1 class="text-2xl font-black">
                    Admin Warung Mas Gondrong
                </h1>
            </div>

            <!-- TOMBOL NAVIGASI -->
            <div class="flex flex-wrap gap-3">
                <a
                    href="index.html"
                    class="rounded-lg bg-zinc-800 px-4 py-3 font-bold hover:bg-zinc-700"
                >
                    🏠 Beranda
                </a>

                <a
                    href="admin.php"
                    class="rounded-lg bg-blue-600 px-4 py-3 font-bold hover:bg-blue-500"
                >
                    📊 Dashboard
                </a>

                <a
                    href="kelola-pesanan.php"
                    class="rounded-lg bg-orange-500 px-4 py-3 font-bold text-black"
                >
                    Kelola Pesanan
                </a>

                <a
                    href="admin.php?logout=1"
                    class="rounded-lg bg-zinc-800 px-4 py-3 font-bold hover:bg-zinc-700"
                >
                    Logout
                </a>
            </div>

        </div>
    </header>

    <main class="mx-auto max-w-6xl space-y-8 p-6">

        <!-- NOTIFIKASI -->
        <?php if (isset($_GET["message_updated"])): ?>
            <div class="rounded-lg bg-green-900 p-4 text-green-200">
                Pesan pelanggan berhasil diperbarui.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["message_deleted"])): ?>
            <div class="rounded-lg bg-green-900 p-4 text-green-200">
                Pesan pelanggan berhasil dihapus.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["order_deleted"])): ?>
            <div class="rounded-lg bg-green-900 p-4 text-green-200">
                Pesanan berhasil dihapus.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["edit_error"])): ?>
            <div class="rounded-lg bg-red-900 p-4 text-red-200">
                Nama dan isi pesan tidak boleh kosong.
            </div>
        <?php endif; ?>

        <!-- RINGKASAN -->
        <section class="grid gap-4 sm:grid-cols-2">

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
                <p class="text-zinc-400">Total Pesanan</p>
                <p class="mt-2 text-4xl font-black text-orange-500">
                    <?= $totalOrders ?>
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
                <p class="text-zinc-400">Total Pesan Pelanggan</p>
                <p class="mt-2 text-4xl font-black text-orange-500">
                    <?= $totalMessages ?>
                </p>
            </div>

        </section>

        <!-- DAFTAR PESANAN -->
        <section>
            <h2 class="mb-4 text-2xl font-black">
                Daftar Pesanan
            </h2>

            <div class="overflow-x-auto rounded-xl border border-zinc-800">
                <table class="w-full text-left">

                    <thead class="bg-zinc-800">
                        <tr>
                            <th class="p-3">ID</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Nama</th>
                            <th class="p-3">Catatan</th>
                            <th class="p-3">Status</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr class="border-t border-zinc-800">

                                <td class="p-3">
                                    <?= (int)$order["id"] ?>
                                </td>

                                <td class="whitespace-nowrap p-3">
                                    <?= e($order["tanggal"]) ?>
                                </td>

                                <td class="p-3">
                                    <?= e($order["nama"]) ?>
                                </td>

                                <td class="p-3">
                                    <?= e($order["catatan"] ?? "") ?>
                                </td>

                                <td class="p-3">
                                    <?= e($order["status"]) ?>
                                </td>

                                <td class="p-3">
                                    <div class="flex justify-end gap-2">

                                        <a
                                            href="kelola-pesanan.php?edit=<?= (int)$order["id"] ?>"
                                            class="rounded-lg bg-blue-600 px-4 py-2 font-bold hover:bg-blue-500"
                                        >
                                            ✏️ Edit
                                        </a>

                                        <form
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus pesanan ini beserta detail menunya?')"
                                        >
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= e($_SESSION["csrf_token"]) ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="order_id"
                                                value="<?= (int)$order["id"] ?>"
                                            >

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
                                <td colspan="6" class="p-5 text-center text-zinc-400">
                                    Belum ada pesanan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </section>

        <!-- PESAN PELANGGAN -->
        <section>
            <h2 class="mb-4 text-2xl font-black">
                Pesan dari Pelanggan
            </h2>

            <!-- FORM EDIT PESAN -->
            <?php if ($editMessage): ?>
                <div class="mb-6 rounded-2xl border border-orange-500 bg-zinc-900 p-6">

                    <h3 class="mb-2 text-2xl font-black text-orange-500">
                        Edit Pesan Pelanggan #<?= (int)$editMessage["id"] ?>
                    </h3>

                    <p class="mb-6 text-zinc-400">
                        <?= e($editMessage["tanggal"]) ?>
                    </p>

                    <form method="POST" action="admin.php" class="space-y-5">

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= e($_SESSION["csrf_token"]) ?>"
                        >

                        <input
                            type="hidden"
                            name="message_id"
                            value="<?= (int)$editMessage["id"] ?>"
                        >

                        <div>
                            <label class="mb-2 block font-bold">
                                Nama Pelanggan
                            </label>

                            <input
                                type="text"
                                name="nama"
                                required
                                maxlength="150"
                                value="<?= e($editMessage["nama"]) ?>"
                                class="w-full rounded-xl border border-zinc-700 bg-black p-3 focus:border-orange-500 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block font-bold">
                                Isi Pesan
                            </label>

                            <textarea
                                name="pesan"
                                rows="5"
                                required
                                maxlength="5000"
                                class="w-full rounded-xl border border-zinc-700 bg-black p-3 focus:border-orange-500 focus:outline-none"
                            ><?= e($editMessage["pesan"]) ?></textarea>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button
                                type="submit"
                                name="update_message"
                                class="rounded-xl bg-orange-500 px-6 py-3 font-black text-black hover:bg-orange-400"
                            >
                                💾 Simpan Perubahan
                            </button>

                            <a
                                href="admin.php"
                                class="rounded-xl bg-zinc-800 px-6 py-3 font-bold hover:bg-zinc-700"
                            >
                                Batal
                            </a>
                        </div>

                    </form>
                </div>
            <?php endif; ?>

            <!-- DAFTAR PESAN -->
            <div class="space-y-4">

                <?php foreach ($messages as $message): ?>
                    <article class="rounded-xl border border-zinc-800 bg-zinc-900 p-5">

                        <div class="flex flex-wrap items-start justify-between gap-4">

                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold">
                                    <?= e($message["nama"]) ?>
                                </h3>

                                <p class="text-sm text-zinc-400">
                                    <?= e($message["tanggal"]) ?>
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2">

                                <a
                                    href="admin.php?edit_message=<?= (int)$message["id"] ?>"
                                    class="rounded-lg bg-blue-600 px-4 py-2 font-bold hover:bg-blue-500"
                                >
                                    ✏️ Edit Pesan
                                </a>

                                <form
                                    method="POST"
                                    action="admin.php"
                                    onsubmit="return confirm('Yakin ingin menghapus pesan ini?')"
                                >
                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= e($_SESSION["csrf_token"]) ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="message_id"
                                        value="<?= (int)$message["id"] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_message"
                                        class="rounded-lg bg-red-600 px-4 py-2 font-bold hover:bg-red-500"
                                    >
                                        🗑️ Hapus Pesan
                                    </button>
                                </form>

                            </div>
                        </div>

                        <p class="mt-5 whitespace-pre-wrap break-words">
                            <?= e($message["pesan"]) ?>
                        </p>

                    </article>
                <?php endforeach; ?>

                <?php if (!$messages): ?>
                    <p class="rounded-xl bg-zinc-900 p-5 text-zinc-400">
                        Belum ada pesan pelanggan.
                    </p>
                <?php endif; ?>

            </div>
        </section>

    </main>

<?php endif; ?>

</body>
</html>