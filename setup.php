<?php

session_start();

$done = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['db_host'] ?? '');
    $db = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = (string)($_POST['db_pass'] ?? '');
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminPass = (string)($_POST['admin_pass'] ?? '');

    try {
        if ($host === '' || $db === '' || $user === '' || $adminUser === '' || $adminPass === '') {
            throw new Exception('Please fill in all required fields.');
        }

        if (strlen($adminPass) < 8) {
            throw new Exception('Admin password must be at least 8 characters.');
        }

        $safeDb = str_replace('`', '', $db);

        $pdo = new PDO(
            "mysql:host={$host};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );

        $pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        $pdo->exec("USE `{$safeDb}`");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS movies (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(180) NOT NULL,
                slug VARCHAR(200) NOT NULL UNIQUE,
                description TEXT,
                genre VARCHAR(120),
                country VARCHAR(100),
                year SMALLINT,
                poster_url VARCHAR(500),
                backdrop_url VARCHAR(500),
                video_url VARCHAR(500),
                download_url VARCHAR(500),
                featured TINYINT(1) NOT NULL DEFAULT 0,
                trending TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // Use nowdoc so PHP does not try to interpolate the generated $pdo variable.
        $configCode = <<<'PHP'
<?php

$pdo = new PDO(
    'mysql:host=__DB_HOST__;dbname=__DB_NAME__;charset=utf8mb4',
    '__DB_USER__',
    '__DB_PASS__',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

const ADMIN_USER = __ADMIN_USER__;
const ADMIN_HASH = __ADMIN_HASH__;
PHP;

        $configCode = str_replace(
            [
                '__DB_HOST__',
                '__DB_NAME__',
                '__DB_USER__',
                '__DB_PASS__',
                '__ADMIN_USER__',
                '__ADMIN_HASH__'
            ],
            [
                addslashes($host),
                addslashes($db),
                addslashes($user),
                addslashes($pass),
                var_export($adminUser, true),
                var_export(password_hash($adminPass, PASSWORD_DEFAULT), true)
            ],
            $configCode
        );

        if (file_put_contents(__DIR__ . '/movie-config.php', $configCode) === false) {
            throw new Exception('Could not create movie-config.php. Check server permissions.');
        }

        $count = (int)$pdo->query('SELECT COUNT(*) FROM movies')->fetchColumn();

        if ($count === 0) {
            $statement = $pdo->prepare("
                INSERT INTO movies
                (title, slug, description, genre, country, year, poster_url, backdrop_url, video_url, featured, trending)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $movies = [
                [
                    'The Last Journey',
                    'the-last-journey',
                    'A powerful African drama about family, courage and the road home.',
                    'Drama', 'Tanzania', 2026,
                    'https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=700&q=85',
                    'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1600&q=85',
                    '', 1, 1
                ],
                [
                    'Mbeya Nights',
                    'mbeya-nights',
                    'A young musician finds a second chance in a city that never sleeps.',
                    'Drama', 'Tanzania', 2026,
                    'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=700&q=85',
                    'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1600&q=85',
                    '', 1, 1
                ],
                [
                    'The Village Code',
                    'the-village-code',
                    'A mysterious discovery changes the future of a remote community.',
                    'Thriller', 'Kenya', 2025,
                    'https://images.unsplash.com/photo-1533929736458-ca588d08c8be?auto=format&fit=crop&w=700&q=85',
                    'https://images.unsplash.com/photo-1500534623283-312aade485b7?auto=format&fit=crop&w=1600&q=85',
                    '', 0, 1
                ],
                [
                    'Queen of the Coast',
                    'queen-of-the-coast',
                    'A coastal legend becomes a modern story of power and identity.',
                    'Adventure', 'Tanzania', 2025,
                    'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=700&q=85',
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=85',
                    '', 1, 0
                ]
            ];

            foreach ($movies as $movie) {
                $statement->execute($movie);
            }
        }

        $done = true;

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AfrikaFlix Setup</title>
    <link rel="stylesheet" href="assets/movie.css">
</head>
<body class="setup-page">
<main class="setup-card">
    <div class="brand">AFRIKA<span>FLIX</span></div>

    <?php if ($done): ?>
        <h1>Platform ready.</h1>
        <p>Database and admin account configured successfully.</p>
        <a class="btn primary" href="index.php">Open AfrikaFlix</a>
    <?php else: ?>
        <h1>Connect your movie database</h1>
        <p>Enter your InfinityFree MySQL details. Credentials are saved only on the server.</p>

        <?php if ($error): ?>
            <div class="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" class="setup-form">
            <label>
                Database host
                <input name="db_host" placeholder="sqlXXX.infinityfree.com" required>
            </label>
            <label>
                Database name
                <input name="db_name" required>
            </label>
            <label>
                Database username
                <input name="db_user" required>
            </label>
            <label>
                Database password
                <input type="password" name="db_pass">
            </label>

            <hr>

            <label>
                Admin username
                <input name="admin_user" value="admin" required>
            </label>
            <label>
                Admin password
                <input type="password" name="admin_pass" minlength="8" required>
            </label>

            <button class="btn primary" type="submit">Install Movie Platform</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
