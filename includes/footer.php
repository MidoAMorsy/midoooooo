</main>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-brand">
                <img src="<?php echo ASSETS_URL; ?>/images/logo-white.png" alt="<?php echo e($siteName); ?>"
                    onerror="this.style.display='none'">
                <h3 style="color:#fff;margin-bottom:1rem;"><?php echo e($siteName); ?></h3>
                <p><?php echo e(getSetting('site_description_' . $currentLang, '')); ?></p>
                <div class="footer-social" style="margin-top:1.5rem;">
                    <?php if ($fb = getSetting('facebook_url')): ?>
                        <a href="<?php echo e($fb); ?>" target="_blank" rel="noopener" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($tw = getSetting('twitter_url')): ?>
                        <a href="<?php echo e($tw); ?>" target="_blank" rel="noopener" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($li = getSetting('linkedin_url')): ?>
                        <a href="<?php echo e($li); ?>" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($ig = getSetting('instagram_url')): ?>
                        <a href="<?php echo e($ig); ?>" target="_blank" rel="noopener" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($yt = getSetting('youtube_url')): ?>
                        <a href="<?php echo e($yt); ?>" target="_blank" rel="noopener" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($gh = getSetting('github_url')): ?>
                        <a href="<?php echo e($gh); ?>" target="_blank" rel="noopener" aria-label="GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div>
                <h4 class="footer-title"><?php echo __('quick_links'); ?></h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/"><?php echo __('home'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php"><?php echo __('about'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php"><?php echo __('services'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/projects.php"><?php echo __('projects'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/blog.php"><?php echo __('blog'); ?></a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php"><?php echo __('contact'); ?></a></li>
                </ul>
            </div>

            <!-- Applications -->
            <div>
                <h4 class="footer-title"><?php echo __('projects'); ?></h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/apps/attendance/"><?php echo __('attendance_system'); ?></a>
                    </li>
                    <li><a href="<?php echo SITE_URL; ?>/apps/qr-generator/"><?php echo __('qr_generator'); ?></a></li>
                    <li><a
                            href="<?php echo SITE_URL; ?>/apps/certificate-creator/"><?php echo __('certificate_creator'); ?></a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div>
                <h4 class="footer-title"><?php echo __('contact_info'); ?></h4>
                <ul class="footer-links">
                    <?php if ($email = getSetting('contact_email')): ?>
                        <li>
                            <a href="mailto:<?php echo e($email); ?>">
                                <i class="fas fa-envelope" style="margin-right:8px;"></i>
                                <?php echo e($email); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($phone = getSetting('contact_phone')): ?>
                        <li>
                            <a href="tel:<?php echo cleanPhoneNumber($phone); ?>">
                                <i class="fas fa-phone" style="margin-right:8px;"></i>
                                <?php echo e($phone); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($address = getSetting('contact_address_' . $currentLang)): ?>
                        <li>
                            <span>
                                <i class="fas fa-map-marker-alt" style="margin-right:8px;"></i>
                                <?php echo e($address); ?>
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo e($siteName); ?>. <?php echo __('copyright'); ?>.</p>
            <div style="display:flex;gap:1rem;">
                <a href="<?php echo SITE_URL; ?>/privacy.php"
                    style="color:var(--gray-500);"><?php echo __('privacy_policy'); ?></a>
                <a href="<?php echo SITE_URL; ?>/terms.php"
                    style="color:var(--gray-500);"><?php echo __('terms_of_service'); ?></a>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="back-to-top" id="back-to-top" aria-label="<?php echo $isRTL ? 'العودة للأعلى' : 'Back to top'; ?>">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Main JavaScript -->
<script src="<?php echo ASSETS_URL; ?>/js/main.js"></script>

<?php if (isset($extraJS)): ?>
    <?php foreach ($extraJS as $js): ?>
        <script src="<?php echo e($js); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inlineJS)): ?>
    <script>
        <?php echo $inlineJS; ?>
    </script>
<?php endif; ?>
</body>

</html>