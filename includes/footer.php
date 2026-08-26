<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <span class="footer-name"><?= e(getSetting('site_title', 'Amanuel Yohannes')) ?></span>
            <span class="footer-role"><?= e(getSetting('designer_title', 'Graphic Designer')) ?></span>
        </div>
        <div class="footer-social">
            <?php $ig = getSetting('social_instagram'); if ($ig): ?>
            <a href="<?= e($ig) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <?php endif; ?>
            <?php $li = getSetting('social_linkedin'); if ($li): ?>
            <a href="<?= e($li) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <?php endif; ?>
            <?php $be = getSetting('social_behance'); if ($be): ?>
            <a href="<?= e($be) ?>" target="_blank" rel="noopener" aria-label="Behance"><i class="bi bi-behance"></i></a>
            <?php endif; ?>
            <?php $tw = getSetting('social_twitter'); if ($tw): ?>
            <a href="<?= e($tw) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
            <?php endif; ?>
            <?php $tg = getSetting('social_telegram'); if ($tg): ?>
            <a href="<?= e($tg) ?>" target="_blank" rel="noopener" aria-label="Telegram"><i class="bi bi-telegram"></i></a>
            <?php endif; ?>
            <?php $fb = getSetting('social_facebook'); if ($fb): ?>
            <a href="<?= e($fb) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <?php endif; ?>
            <?php $tt = getSetting('social_tiktok'); if ($tt): ?>
            <a href="<?= e($tt) ?>" target="_blank" rel="noopener" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
            <?php endif; ?>
            <?php $yt = getSetting('social_youtube'); if ($yt): ?>
            <a href="<?= e($yt) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
            <?php endif; ?>
        </div>
        <div class="footer-copy"><?= e(getSetting('copyright_text', '© 2026 Amanuel Yohannes')) ?></div>
        <div class="footer-powered">Powered by <a href="mailto:sayhi@akiyacrm.com" class="footer-powered-link"><i class="bi bi-stars"></i><span>labratsai</span></a></div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/public.js"></script>
</body>
</html>
