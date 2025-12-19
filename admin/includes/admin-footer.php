</div><!-- /.admin-main -->

<!-- Admin JavaScript -->
<script src="<?php echo ASSETS_URL; ?>/js/admin.js"></script>

<?php if (isset($extraJS)): ?>
    <?php foreach ($extraJS as $js): ?>
        <script src="<?php echo e($js); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inlineJS)): ?>
    <script><?php echo $inlineJS; ?></script>
<?php endif; ?>
</body>

</html>