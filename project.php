<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$project = getProjectBySlug($slug);
if (!$project) {
    http_response_code(404);
    $pageTitle = '404 – Not Found';
    include __DIR__ . '/includes/header.php';
    echo '<div style="padding:12rem 2.5rem;text-align:center;">';
    echo '<p style="font-size:0.7rem;letter-spacing:0.2em;text-transform:uppercase;color:var(--muted);margin-bottom:1rem;">404</p>';
    echo '<h1 style="font-family:var(--font-serif);font-size:3rem;font-weight:300;margin-bottom:1.5rem;">Project Not Found</h1>';
    echo '<a href="' . BASE_URL . '/" style="font-size:0.8rem;letter-spacing:0.15em;text-transform:uppercase;border-bottom:1px solid currentColor;padding-bottom:2px;">← Back to Work</a>';
    echo '</div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$media    = getProjectMedia($project['id']);
$adjacent = getAdjacentProjects($project['id'], $project['display_order']);
$pageTitle = $project['title'];

include __DIR__ . '/includes/header.php';

$imageMedia = array_values(array_filter($media, fn($m) => $m['type'] === 'image'));
$videoMedia = array_values(array_filter($media, fn($m) => $m['type'] === 'video'));
?>

<div class="project-page">

<!-- PROJECT HERO -->
<?php if ($project['cover_image']): ?>
<div class="project-hero">
    <div class="project-hero-bg">
        <img src="<?= e(assetUrl($project['cover_image'])) ?>" alt="<?= e($project['title']) ?>">
    </div>
    <div class="project-hero-overlay"></div>
    <div class="project-hero-content">
        <div class="project-breadcrumb">
            <a href="<?= BASE_URL ?>/">Work</a><span>/</span>
            <?php if ($project['category_name']): ?><span><?= e($project['category_name']) ?></span><span>/</span><?php endif; ?>
            <span><?= e($project['title']) ?></span>
        </div>
        <h1 class="project-hero-title"><?= e($project['title']) ?></h1>
        <div class="project-hero-meta">
            <?php if ($project['client_name']): ?>
            <div class="project-meta-item"><span class="project-meta-label">Client</span><span class="project-meta-value"><?= e($project['client_name']) ?></span></div>
            <?php endif; ?>
            <?php if ($project['year']): ?>
            <div class="project-meta-item"><span class="project-meta-label">Year</span><span class="project-meta-value"><?= e($project['year']) ?></span></div>
            <?php endif; ?>
            <?php if ($project['category_name']): ?>
            <div class="project-meta-item"><span class="project-meta-label">Category</span><span class="project-meta-value"><?= e($project['category_name']) ?></span></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="project-hero-simple">
    <div class="project-breadcrumb">
        <a href="<?= BASE_URL ?>/">Work</a><span>/</span>
        <?php if ($project['category_name']): ?><span><?= e($project['category_name']) ?></span><span>/</span><?php endif; ?>
        <span><?= e($project['title']) ?></span>
    </div>
    <h1 class="project-hero-title"><?= e($project['title']) ?></h1>
    <div class="project-hero-meta" style="margin-top:1.5rem;">
        <?php if ($project['client_name']): ?>
        <div class="project-meta-item"><span class="project-meta-label">Client</span><span class="project-meta-value"><?= e($project['client_name']) ?></span></div>
        <?php endif; ?>
        <?php if ($project['year']): ?>
        <div class="project-meta-item"><span class="project-meta-label">Year</span><span class="project-meta-value"><?= e($project['year']) ?></span></div>
        <?php endif; ?>
        <?php if ($project['category_name']): ?>
        <div class="project-meta-item"><span class="project-meta-label">Category</span><span class="project-meta-value"><?= e($project['category_name']) ?></span></div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- PINTEREST GALLERY -->
<?php if ($imageMedia || $videoMedia): ?>
<div class="project-gallery-section">
  <div class="pin-grid" id="pinGrid">
    <?php foreach ($imageMedia as $i => $img): ?>
    <div class="pin-item fade-up" style="transition-delay:<?= min($i * 0.07, 0.5) ?>s">
      <div class="pin-frame">
        <img src="<?= e(assetUrl($img['file_path'])) ?>" alt="<?= e($img['alt_text'] ?: $project['title']) ?>" <?= $i === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>>
        <div class="pin-overlay">
          <button class="pin-zoom" data-src="<?= e(assetUrl($img['file_path'])) ?>" data-alt="<?= e($img['alt_text'] ?: $project['title']) ?>" aria-label="View full size">
            <i class="bi bi-arrows-fullscreen"></i>
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <?php foreach ($videoMedia as $i => $vid): ?>
    <?php $src = e(assetUrl($vid['file_path'])); ?>
    <div class="pin-item pin-item--video fade-up" style="transition-delay:<?= min(($i + count($imageMedia)) * 0.07, 0.5) ?>s">
      <div class="pin-frame pin-frame--video">
        <video class="pin-video-el" src="<?= $src ?>" preload="metadata" muted playsinline loop></video>
        <div class="pin-video-poster"><i class="bi bi-play-circle-fill"></i></div>
        <div class="pin-overlay">
          <button class="pin-zoom pin-video-play" data-src="<?= $src ?>" aria-label="Play video">
            <i class="bi bi-play-fill"></i>
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Lightbox overlay -->
  <div id="pinLightbox">
    <button id="pinLightboxClose" aria-label="Close"><i class="bi bi-x-lg"></i></button>
    <button id="pinLightboxPrev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
    <button id="pinLightboxNext" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
    <img id="pinLightboxImg" src="" alt="">
    <video id="pinLightboxVideo" preload="metadata" controls playsinline style="display:none;max-width:92vw;max-height:92vh;border-radius:12px;"></video>
  </div>

  <script>
  window.PIN_IMAGES = [
    <?php foreach ($imageMedia as $img): ?>
    { src: "<?= e(assetUrl($img['file_path'])) ?>", alt: "<?= e(addslashes($img['alt_text'] ?: $project['title'])) ?>", type: 'image' },
    <?php endforeach; ?>
    <?php foreach ($videoMedia as $vid): ?>
    { src: "<?= e(assetUrl($vid['file_path'])) ?>", alt: "", type: 'video' },
    <?php endforeach; ?>
  ];
  </script>
</div>
<?php endif; ?>

<!-- PROJECT BODY -->
<?php if ($project['description'] || $project['short_description']): ?>
<div class="project-body">
    <aside class="project-sidebar">
        <?php if ($project['client_name']): ?>
        <div class="project-sidebar-item">
            <p class="project-sidebar-label">Client</p>
            <p class="project-sidebar-value"><?= e($project['client_name']) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($project['year']): ?>
        <div class="project-sidebar-item">
            <p class="project-sidebar-label">Year</p>
            <p class="project-sidebar-value"><?= e($project['year']) ?></p>
        </div>
        <?php endif; ?>
        <?php if ($project['category_name']): ?>
        <div class="project-sidebar-item">
            <p class="project-sidebar-label">Category</p>
            <p class="project-sidebar-value"><?= e($project['category_name']) ?></p>
        </div>
        <?php endif; ?>
        <div class="project-sidebar-back">
            <a href="<?= BASE_URL ?>/" style="font-size:0.75rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--muted);border-bottom:1px solid var(--rule);padding-bottom:2px;transition:color 0.2s;">
                ← All Work
            </a>
        </div>
    </aside>
    <div>
        <?php $desc = $project['description'] ?: $project['short_description']; ?>
        <div class="project-desc">
            <?php
            $paras = array_filter(explode("\n", $desc));
            foreach ($paras as $para):
                $para = trim($para);
                if ($para) echo '<p>' . e($para) . '</p>';
            endforeach;
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- PROJECT NAVIGATION -->
<nav class="project-nav">
    <?php if ($adjacent['prev']): ?>
    <a href="<?= BASE_URL ?>/project?slug=<?= e($adjacent['prev']['slug']) ?>" class="project-nav-item">
        <span class="project-nav-dir">← Previous</span>
        <span class="project-nav-title"><?= e($adjacent['prev']['title']) ?></span>
        <?php if ($adjacent['prev']['cover_image']): ?>
        <img src="<?= e(assetUrl($adjacent['prev']['cover_image'])) ?>" alt="" class="project-nav-thumb" loading="lazy">
        <?php endif; ?>
    </a>
    <?php else: ?><div></div><?php endif; ?>

    <?php if ($adjacent['next']): ?>
    <a href="<?= BASE_URL ?>/project?slug=<?= e($adjacent['next']['slug']) ?>" class="project-nav-item next" style="text-align:right;align-items:flex-end;">
        <span class="project-nav-dir">Next →</span>
        <span class="project-nav-title"><?= e($adjacent['next']['title']) ?></span>
        <?php if ($adjacent['next']['cover_image']): ?>
        <img src="<?= e(assetUrl($adjacent['next']['cover_image'])) ?>" alt="" class="project-nav-thumb" loading="lazy">
        <?php endif; ?>
    </a>
    <?php else: ?><div></div><?php endif; ?>
</nav>

</div><!-- /.project-page -->

<?php include __DIR__ . '/includes/footer.php'; ?>
