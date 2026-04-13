<?php
/**
 * Bootstrap 5 JS + diálogos de aplicación (ui-dialog.js).
 * Requiere $basePath (p. ej. /hamilton-store/public).
 */
if (!isset($basePath)) {
    $basePath = '';
}
if ($basePath === '/' || $basePath === '\\') {
    $basePath = '';
}
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars($basePath); ?>/js/ui-dialog.js"></script>
