<footer class="site-footer">
    <p>© <?php echo date('Y'); ?> NutriVert - BackOffice / FrontOffice</p>
</footer>

<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <h3><?php echo t('confirmation'); ?></h3>
        <p><?php echo t('delete_confirm'); ?></p>

        <div class="delete-modal-actions">
            <button type="button" id="cancelDelete" class="btn-cancel"><?php echo t('cancel'); ?></button>
            <a href="#" id="confirmDeleteBtn" class="btn-confirm"><?php echo t('confirm'); ?></a>
        </div>
    </div>
</div>

<script src="assets/js/app.js?v=4"></script>
</body>
</html>
