<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Bomatrack'; ?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <meta name="color-scheme" content="light dark">
</head>
<body>
    <?php include __DIR__ . '/header.php'; ?>

    <main class="container">
        <?php echo $content; ?>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>