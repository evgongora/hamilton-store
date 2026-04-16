<?php
/**
 * head.php - Meta tags, CSS, favicon
 * Requiere: $pageTitle (string), $basePath (string, ej: /hamilton-store/public)
 */
$pageTitle = $pageTitle ?? 'M. Hamilton Store';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<link rel="icon" type="image/x-icon" href="<?php echo htmlspecialchars($basePath); ?>/assets/img/favicon.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?php echo htmlspecialchars($basePath); ?>/css/styles.css" rel="stylesheet">
