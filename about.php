<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$about     = getAbout();
$services  = getActiveServices();
$favorites = getFavoriteProjects();
$pageTitle = 'About';

// Real stats from DB
$statProjects = (int) db()->query('SELECT COUNT(*) FROM projects WHERE status = "published"')->fetchColumn();
$statClients  = (int) db()->query('SELECT COUNT(*) FROM clients WHERE status = "active"')->fetchColumn();
$statServices = (int) db()->query('SELECT COUNT(*) FROM services WHERE status = "active"')->fetchColumn();
// Year range: earliest project year to now
$earliestYear = (int) db()->query('SELECT MIN(year) FROM projects WHERE status = "published" AND year IS NOT NULL')->fetchColumn();
$yearsExp     = $earliestYear ? (date('Y') - $earliestYear) : 0;

include __DIR__ . '/includes/header.php';
?>

<!-- ── HERO ──────────────────────────────────────────────── -->
<section class="ap-hero">
    <div class="ap-hero-inner">
        <div class="ap-hero-image" id="apImg">
            <?php
            $profileImg     = ($about['profile_image'] ?? '') ?: getSetting('profile_image');
            $profileImgDark = $about['profile_image_dark'] ?? '';
            ?>
            <?php if ($profileImg || $profileImgDark): ?>
            <?php if ($profileImg): ?>
            <img src="<?= e(assetUrl($profileImg)) ?>" alt="<?= e($about['name'] ?? '') ?>" class="about-img-light">
            <?php endif; ?>
            <?php if ($profileImgDark): ?>
            <img src="<?= e(assetUrl($profileImgDark)) ?>" alt="<?= e($about['name'] ?? '') ?>" class="about-img-dark">
            <?php endif; ?>
            <?php else: ?>
            <div class="ap-hero-placeholder"><i class="bi bi-person"></i></div>
            <?php endif; ?>
            <div class="ap-hero-image-label">
                <span><?= e($about['title'] ?? 'Graphic Designer') ?></span>
            </div>
        </div>

        <div class="ap-hero-content" id="apContent">
            <p class="ap-eyebrow">About</p>
            <h1 class="ap-name">
                <?php
                $parts = explode(' ', $about['name'] ?? 'Amanuel Yohannes', 2);
                echo e($parts[0]);
                if (!empty($parts[1])) echo ' <em>' . e($parts[1]) . '</em>';
                ?>
                <span class="ap-name-mark"></span>
            </h1>

            <?php if (!empty($about['biography'])): ?>
            <div class="ap-bio">
                <?php
                $paras = array_filter(array_map('trim', explode("\n", $about['biography'])));
                foreach ($paras as $para):
                    echo '<p><span class="ap-bio-mark"></span>' . e($para) . '</p>';
                endforeach;
                ?>
            </div>
            <?php endif; ?>

            <div class="ap-actions">
                <a href="<?= BASE_URL ?>/contact" class="btn-outline">
                    Start a Project <i class="bi bi-arrow-right"></i>
                </a>
                <a href="<?= BASE_URL ?>/#portfolio" class="ap-work-link">
                    View Work <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ── STATS STRIP ────────────────────────────────────────── -->
<div class="ap-stats">
    <div class="ap-stats-inner">
        <div class="ap-stat">
            <span class="ap-stat-num" data-count="100" data-suffix="%">0%</span>
            <span class="ap-stat-label">Client Satisfaction</span>
        </div>
        <div class="ap-stat-divider"></div>
        <div class="ap-stat">
            <span class="ap-stat-num" data-count="<?= $statProjects ?>" data-suffix="+">0+</span>
            <span class="ap-stat-label">Projects Delivered</span>
        </div>
        <div class="ap-stat-divider"></div>
        <div class="ap-stat">
            <span class="ap-stat-num" data-count="<?= $statClients ?>" data-suffix="+">0+</span>
            <span class="ap-stat-label">Clients Served</span>
        </div>
        <div class="ap-stat-divider"></div>
        <div class="ap-stat">
            <span class="ap-stat-num" data-count="<?= $statServices ?>" data-suffix="">0</span>
            <span class="ap-stat-label">Services Offered</span>
        </div>
    </div>
</div>

<!-- ── FAVOURITE WORK ─────────────────────────────────────── -->
<?php if ($favorites): ?>
<section class="section ap-fav-section">
    <div class="section-inner">
        <div class="section-header">
            <h2 class="section-title">Favourite Work</h2>
            <span class="section-label">Selected Projects</span>
        </div>
        <div class="ap-fav-grid">
            <?php foreach ($favorites as $i => $p): ?>
            <a href="<?= BASE_URL ?>/project?slug=<?= e($p['slug']) ?>" class="ap-fav-card fade-up" style="transition-delay:<?= $i * 0.08 ?>s">
                <div class="ap-fav-img-wrap">
                    <?php if ($p['cover_image']): ?>
                    <img src="<?= e(assetUrl($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="ap-fav-img-placeholder"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                    <div class="ap-fav-overlay">
                        <span class="ap-fav-cta">View Project <i class="bi bi-arrow-up-right"></i></span>
                    </div>
                </div>
                <div class="ap-fav-info">
                    <?php if ($p['category_name']): ?>
                    <span class="ap-fav-cat"><?= e($p['category_name']) ?></span>
                    <?php endif; ?>
                    <h3 class="ap-fav-title"><?= e($p['title']) ?></h3>
                    <?php if ($p['year']): ?>
                    <span class="ap-fav-year"><?= e($p['year']) ?></span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── SERVICES ───────────────────────────────────────────── -->
<?php if ($services): ?>
<section class="section services-section">
    <div class="section-inner">
        <div class="section-header">
            <h2 class="section-title">What We Do</h2>
            <span class="section-label">Services</span>
        </div>
        <div class="services-grid">
            <?php foreach ($services as $i => $svc): ?>
            <div class="service-item">
                <span class="service-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                <div class="service-body">
                    <h3 class="service-title"><?= e($svc['title']) ?></h3>
                    <?php if ($svc['description']): ?>
                    <p class="service-desc"><?= e($svc['description']) ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($svc['icon']): ?>
                <div class="service-icon"><i class="bi <?= e($svc['icon']) ?>"></i></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="ap-cta-section">
    <div class="section-inner">
        <p class="t-subheading" style="display:block;margin-bottom:1.5rem;">Ready to Start?</p>
        <h2 class="ap-cta-heading">Let's create something <em>exceptional.</em></h2>
        <a href="<?= BASE_URL ?>/contact" class="btn-outline">
            Start a Project <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
