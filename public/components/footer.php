<?php
/**
 * footer.php - Pie de página del sistema
 * Define window.API_BASE para fetch al backend (backend/config/paths.php).
 */
if (!isset($apiBase)) {
    $pathsFile = __DIR__ . '/../../backend/config/paths.php';
    $apiBase = is_file($pathsFile) ? (require $pathsFile)['api'] : '';
}
?>
<script>
window.API_BASE = <?php echo json_encode($apiBase, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
</script>
<footer class="footer mt-auto py-3 bg-light border-top">
    <div class="container">
        <p class="m-0 text-center text-muted small">Copyright &copy; M. Hamilton Store <?php echo date('Y'); ?></p>
    </div>
</footer>
