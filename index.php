<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$contactSuccess  = false;
$contactErrors   = [];
$contactData     = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    verifyCsrf();
    $contactData['name']    = trim($_POST['name']    ?? '');
    $contactData['email']   = trim($_POST['email']   ?? '');
    $contactData['subject'] = trim($_POST['subject'] ?? '');
    $contactData['message'] = trim($_POST['message'] ?? '');

    if (strlen($contactData['name']) < 2)                          $contactErrors[] = 'Please enter your name.';
    if (!filter_var($contactData['email'], FILTER_VALIDATE_EMAIL)) $contactErrors[] = 'Please enter a valid email address.';
    if (strlen($contactData['message']) < 10)                      $contactErrors[] = 'Please enter a message (at least 10 characters).';

    if (!$contactErrors) {
        $toEmail     = getSetting('contact_email');
        $fromName    = getSetting('smtp_from_name',  'Amanuel Yohannes Portfolio');
        $fromEmail   = getSetting('smtp_from_email', 'noreply@portfolio.local');
        $smtpEnabled = getSetting('smtp_enabled', '0') === '1';
        $subject     = $contactData['subject'] ?: 'Portfolio Enquiry from ' . $contactData['name'];
        $plainBody   = "Name: {$contactData['name']}\nEmail: {$contactData['email']}\nSubject: " . ($contactData['subject'] ?: '—') . "\n\nMessage:\n{$contactData['message']}";
        $htmlBody    = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;color:#222;"><div style="background:#0a0a0a;padding:24px 32px;"><p style="color:#fff;font-size:13px;letter-spacing:0.15em;text-transform:uppercase;margin:0;">Amanuel Yohannes — Portfolio</p></div><div style="padding:32px;background:#fafafa;border:1px solid #e8e8e8;"><h2 style="font-size:20px;font-weight:400;margin:0 0 24px;">New Contact Message</h2><p style="font-size:15px;line-height:1.7;white-space:pre-wrap;">' . htmlspecialchars($contactData['message']) . '</p></div></div>';
        $sent = false;
        if ($toEmail) {
            if ($smtpEnabled) {
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = getSetting('smtp_host');
                    $mail->SMTPAuth   = true;
                    $mail->Username   = getSetting('smtp_username');
                    $mail->Password   = getSetting('smtp_password');
                    $enc = getSetting('smtp_encryption', 'tls');
                    $mail->SMTPSecure = $enc === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = (int) getSetting('smtp_port', '587');
                    $mail->CharSet    = 'UTF-8';
                    $mail->setFrom($fromEmail, $fromName);
                    $mail->addAddress($toEmail);
                    $mail->addReplyTo($contactData['email'], $contactData['name']);
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body    = $htmlBody;
                    $mail->AltBody = $plainBody;
                    $mail->send();
                    $sent = true;
                } catch (Exception $e) {
                    error_log('Contact form mail error: ' . $e->getMessage());
                }
            } else {
                $headers  = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                $headers .= "From: {$fromName} <{$fromEmail}>\r\nReply-To: {$contactData['email']}\r\n";
                $sent = mail($toEmail, $subject, $plainBody, $headers);
            }
        } else {
            $sent = true;
        }
        if ($sent) {
            $contactSuccess = true;
            $contactData    = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        } else {
            $contactErrors[] = 'Sorry, your message could not be sent. Please try again or contact directly by email.';
        }
    }
}

$projects    = getPublishedProjects();
$categories  = getCategories();
$services    = getActiveServices();
$clients     = getActiveClients();
$sponsors    = getActiveSponsors();
$about       = getAbout();
$favorites   = getFavoriteProjects();

$statProjects = (int) db()->query('SELECT COUNT(*) FROM projects WHERE status = "published"')->fetchColumn();
$statClients  = (int) db()->query('SELECT COUNT(*) FROM clients WHERE status = "active"')->fetchColumn();
$statServices = (int) db()->query('SELECT COUNT(*) FROM services WHERE status = "active"')->fetchColumn();

$heroTitle    = getSetting('hero_title',    'Design That Speaks.');
$heroSubtitle = getSetting('hero_subtitle', 'Editorial design, brand identity, and print communications — crafted with precision and purpose.');
$heroCtaText  = getSetting('hero_cta_text', 'View Work');
$showClients  = getSetting('show_clients',  '1');
$showAbout    = getSetting('show_about',    '1');
$showServices = getSetting('show_services', '1');
$showSponsors = getSetting('show_sponsors', '1');

$pageTitle = getSetting('site_title');
include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-content">
        <p class="hero-label"><span><?= e(getSetting('designer_title', 'Graphic Designer')) ?></span><span class="hero-label-sep">·</span><span><?= e(getSetting('contact_location', 'Addis Ababa')) ?></span></p>
        <h1 class="hero-title"><?= e($heroTitle) ?></h1>
        <p class="hero-sub"><?= e($heroSubtitle) ?></p>
        <a href="#portfolio" class="hero-cta">
            <?= e($heroCtaText) ?> <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <img src="<?= BASE_URL ?>/side/fav.png" alt="" class="stats-side-img" aria-hidden="true">
    <div class="hero-video-outer" id="heroVideo">
        <div class="hero-video-wrap">
            <img src="<?= BASE_URL ?>/side/cropped_circle_image.png" class="hero-video-poster" alt="" aria-hidden="true">
            <video autoplay muted loop playsinline preload="auto" oncontextmenu="return false;" oncanplay="this.previousElementSibling.style.opacity='0'">
                <source src="<?= BASE_URL ?>/dashvideo1.mp4" type="video/mp4">
            </video>
        </div>
    </div>
    <div class="hero-scroll-hint">
        <div class="scroll-line"></div>
        <span>Scroll</span>
    </div>
</section>

<!-- PORTFOLIO -->
<section class="section portfolio-section" id="portfolio">
    <div class="section-inner">
        <div class="section-header pf-section-header">
            <h2 class="pf-section-title" data-text="Projects">Projects</h2>
            <span class="section-label">Selected Projects</span>
        </div>

        <?php if ($categories): ?>
        <div class="filter-dropdown-wrap" style="margin-bottom:2rem;">
            <div class="filter-dropdown" id="filterDropdown">
                <button class="filter-dropdown-btn" id="filterDropdownBtn" aria-haspopup="listbox" aria-expanded="false">
                    <span id="filterDropdownLabel">All</span>
                    <i class="bi bi-chevron-down filter-dropdown-chevron"></i>
                </button>
                <ul class="filter-dropdown-list" id="filterDropdownList" role="listbox">
                    <li class="filter-dropdown-item active" data-cat="all" role="option" aria-selected="true">All</li>
                    <?php foreach ($categories as $cat): ?>
                    <li class="filter-dropdown-item" data-cat="<?= e($cat['slug']) ?>" role="option" aria-selected="false"><?= e($cat['name']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($projects): ?>
        <div class="pf-stack" id="pfStack">
            <?php foreach ($projects as $i => $p): ?>
            <div class="pf-card" data-cat="<?= e($p['category_slug'] ?? '') ?>" data-index="<?= $i ?>">
                <a href="<?= BASE_URL ?>/project?slug=<?= e($p['slug']) ?>" class="pf-card-inner">
                    <div class="pf-card-img">
                        <?php if ($p['cover_image']): ?>
                        <img src="<?= e(assetUrl($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>" <?= $i === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"' ?>>
                        <?php else: ?>
                        <div class="pf-card-img-placeholder"><i class="bi bi-image"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="pf-card-info">
                        <div class="pf-card-meta">
                            <?php if ($p['category_name']): ?>
                            <span class="pf-card-cat"><?= e($p['category_name']) ?></span>
                            <?php endif; ?>
                            <?php if ($p['year']): ?>
                            <span class="pf-card-year"><?= e($p['year']) ?></span>
                            <?php endif; ?>
                        </div>
                        <h3 class="pf-card-title"><?= e($p['title']) ?></h3>
                        <?php if ($p['short_description']): ?>
                        <p class="pf-card-desc"><?= e($p['short_description']) ?></p>
                        <?php endif; ?>
                        <span class="pf-card-cta">View Project <i class="bi bi-arrow-up-right"></i></span>
                    </div>
                    <span class="pf-card-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="pf-stack-ctrl" id="pfStackCtrl"></div>
        <?php else: ?>
        <p style="color:var(--muted);font-size:0.9rem;text-align:center;padding:4rem 0;">No projects published yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- STATS STRIP -->
<?php if ($showAbout && $about): ?>
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
<?php endif; ?>

<!-- FAVOURITE WORK -->
<?php if ($favorites): ?>
<section class="section ap-fav-section" id="favourite-work">
    <div class="section-inner">
        <div class="section-header">
            <h2 class="section-title">Favourite Work</h2>
            <span class="section-label">Selected Projects</span>
        </div>
        <div class="pin-grid fav-pin-grid" id="favPinGrid">
            <?php foreach ($favorites as $i => $p): ?>
            <div class="pin-item fade-up" style="transition-delay:<?= min($i * 0.07, 0.5) ?>s">
                <div class="pin-frame">
                    <?php if ($p['cover_image']): ?>
                    <img src="<?= e(assetUrl($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="ap-fav-img-placeholder"><i class="bi bi-image"></i></div>
                    <?php endif; ?>
                    <div class="pin-overlay">
                        <button class="pin-zoom fav-pin-zoom" data-href="<?= BASE_URL ?>/project?slug=<?= e($p['slug']) ?>" data-src="<?= e(assetUrl($p['cover_image'] ?? '')) ?>" data-alt="<?= e($p['title']) ?>" aria-label="View project">
                            <i class="bi bi-arrow-up-right"></i>
                        </button>
                    </div>
                </div>
                <div class="ap-fav-info" style="padding:0.6rem 0.25rem 0;">
                    <?php if ($p['category_name']): ?>
                    <span class="ap-fav-cat"><?= e($p['category_name']) ?></span>
                    <?php endif; ?>
                    <h3 class="ap-fav-title"><a href="<?= BASE_URL ?>/project?slug=<?= e($p['slug']) ?>" style="color:inherit;"><?= e($p['title']) ?></a></h3>
                    <?php if ($p['year']): ?>
                    <span class="ap-fav-year"><?= e($p['year']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<script>
(function(){
  document.querySelectorAll('.fav-pin-grid .pin-frame').forEach(function(frame){
    frame.style.cursor = 'pointer';
    frame.addEventListener('click', function(){
      var btn = frame.querySelector('.fav-pin-zoom');
      if (btn) window.location.href = btn.dataset.href;
    });
  });
  document.querySelectorAll('.fav-pin-zoom').forEach(function(btn){
    btn.addEventListener('click', function(e){
      e.stopPropagation();
      window.location.href = btn.dataset.href;
    });
  });
})();
</script>
<?php endif; ?>
<?php if ($showSponsors && $sponsors): ?>
<section class="section sponsors-section" id="sponsors" style="padding-top:4rem;padding-bottom:4rem;">
    <div class="section-inner">
        <div class="section-header pf-section-header" style="margin-bottom:2.5rem;">
            <h2 class="pf-section-title pf-section-title--sm" data-text="Proudly Supported By">Proudly Supported By</h2>
        </div>
        <div class="sponsors-marquee-wrap">
            <div class="sponsors-marquee" id="sponsorsMarquee">
                <?php for ($pass = 0; $pass < 2; $pass++): foreach ($sponsors as $sp): ?>
                <?php if (!$sp['logo']) continue; ?>
                <div class="sponsor-entry">
                    <img src="<?= e(assetUrl($sp['logo'])) ?>" alt="" class="sponsor-logo-img" loading="lazy">
                </div>
                <?php endforeach; endfor; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ABOUT -->
<?php if ($showAbout && $about): ?>
<section class="section about-section" id="about">
    <div class="section-inner">
        <div class="about-grid">
            <div class="about-image-wrap" id="aboutImg">
                <?php
                $profileImg     = $about['profile_image'] ?: getSetting('profile_image');
                $profileImgDark = $about['profile_image_dark'] ?? '';
                ?>
                <?php if ($profileImg || $profileImgDark): ?>
                <?php if ($profileImg): ?>
                <img src="<?= e(assetUrl($profileImg)) ?>" alt="<?= e($about['name']) ?>" loading="lazy" class="about-img-light">
                <?php endif; ?>
                <?php if ($profileImgDark): ?>
                <img src="<?= e(assetUrl($profileImgDark)) ?>" alt="<?= e($about['name']) ?>" loading="lazy" class="about-img-dark">
                <?php endif; ?>
                <?php else: ?>
                <div class="about-image-placeholder"><i class="bi bi-person"></i></div>
                <?php endif; ?>
            </div>
            <div class="about-content" id="aboutContent">
                <p class="about-eyebrow">About</p>
                <h2 class="about-name">
                    <?php
                    $nameParts = explode(' ', $about['name'], 2);
                    echo e($nameParts[0]);
                    if (!empty($nameParts[1])) echo ' <em>' . e($nameParts[1]) . '</em>';
                    ?>
                    <span class="about-name-mark"></span>
                </h2>
                <?php if ($about['title']): ?>
                <p class="t-subheading mb-4"><?= e($about['title']) ?></p>
                <?php endif; ?>
                <?php if ($about['biography']): ?>
                <div class="about-bio">
                    <?php
                    $paras = array_filter(explode("\n", nl2br(e($about['biography']))));
                    foreach ($paras as $para):
                        $para = trim($para);
                        if ($para) echo '<p><span class="about-bio-mark"></span>' . $para . '</p>';
                    endforeach;
                    ?>
                </div>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/about.php" class="btn-outline">
                    More About Me <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- SERVICES -->
<?php if ($showServices && $services): ?>
<section class="section services-section" id="services">
    <div class="section-inner">
        <div class="section-header pf-section-header">
            <h2 class="pf-section-title" data-text="Services">Services</h2>
            <span class="section-label">What We Do</span>
        </div>
        <div class="services-grid">
            <?php foreach ($services as $i => $svc): ?>
            <div class="service-item">
                <span class="service-num"><?= str_pad($i + 1, 2, '0', STR_PAD_LEFT) ?></span>
                <div class="service-body">
                    <div class="service-body-text">
                        <h3 class="service-title"><?= e($svc['title']) ?></h3>
                        <?php if ($svc['description']): ?>
                        <p class="service-desc"><?= e($svc['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($svc['icon']): ?>
                    <div class="service-icon"><i class="bi <?= e($svc['icon']) ?>"></i></div>
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

<!-- DARK END BLOCK: Clients + Divider + Contact + Footer -->
<div class="dark-end-block">

<!-- CLIENTS / TESTIMONIALS -->
<?php if ($showClients && $clients): ?>
<?php $testimonials = array_filter($clients, fn($c) => !empty($c['testimonial'] ?? '')); ?>
<?php if ($testimonials): ?>
<section class="section testimonials-section" id="clients">
    <div class="section-inner">
        <div class="section-header pf-section-header">
            <h2 class="pf-section-title" data-text="Clients">Clients</h2>
            <span class="section-label">Client Testimonials</span>
        </div>
        <div class="testimonials-marquee-wrap">
            <div class="tcard-row" id="tcardRow1">
                <?php $tArr = array_values($testimonials); $half = (int)ceil(count($tArr)/2); ?>
                <?php foreach (array_slice($tArr, 0, $half) as $client): ?>
                <div class="tcard">
                    <?php if ($client['logo']): ?>
                    <img src="<?= e(assetUrl($client['logo'])) ?>" alt="<?= e($client['name']) ?>" class="tcard-avatar">
                    <?php else: ?>
                    <div class="tcard-avatar tcard-avatar--initials"><?= e(mb_substr($client['name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="tcard-body">
                        <div class="tcard-quote">&ldquo;</div>
                        <p class="tcard-text"><?= e($client['testimonial']) ?></p>
                        <div class="tcard-author-info">
                            <span class="tcard-name"><?= e($client['name']) ?></span>
                            <?php if (!empty($client['person_title'])): ?>
                            <span class="tcard-title"><?= e($client['person_title']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="tcard-row" id="tcardRow2">
                <?php foreach (array_slice($tArr, $half) as $client): ?>
                <div class="tcard">
                    <?php if ($client['logo']): ?>
                    <img src="<?= e(assetUrl($client['logo'])) ?>" alt="<?= e($client['name']) ?>" class="tcard-avatar">
                    <?php else: ?>
                    <div class="tcard-avatar tcard-avatar--initials"><?= e(mb_substr($client['name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <div class="tcard-body">
                        <div class="tcard-quote">&ldquo;</div>
                        <p class="tcard-text"><?= e($client['testimonial']) ?></p>
                        <div class="tcard-author-info">
                            <span class="tcard-name"><?= e($client['name']) ?></span>
                            <?php if (!empty($client['person_title'])): ?>
                            <span class="tcard-title"><?= e($client['person_title']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php else: ?>
<section class="section clients-section" id="clients" style="padding-top:4rem;padding-bottom:4rem;">
    <div class="section-inner">
        <div class="section-header pf-section-header" style="margin-bottom:2.5rem;">
            <h2 class="pf-section-title" data-text="Clients">Clients</h2>
            <span class="section-label">Trusted By</span>
        </div>
        <div class="clients-marquee-wrap">
            <div class="clients-marquee" id="clientsMarquee">
                <?php for ($pass = 0; $pass < 2; $pass++): foreach ($clients as $client): ?>
                <div class="client-entry">
                    <?php if ($client['logo']): ?>
                    <img src="<?= e(assetUrl($client['logo'])) ?>" alt="<?= e($client['name']) ?>" class="client-logo-img" loading="lazy">
                    <?php else: ?>
                    <span class="client-name-text"><?= e($client['name']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; endfor; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
<?php endif; ?>

<!-- GET IN TOUCH DIVIDER -->
<div class="section-divider-title">
    <h2 class="pf-section-title pf-section-title--sm pf-title--filling" data-text="Get In Touch">Get In Touch</h2>
</div>

<!-- CONTACT -->
<section class="section contact-section" id="contact">
    <div class="cp-wrap" style="padding:0;max-width:100%;">
        <div class="section-inner">
            <div class="cp-availability" style="text-align:center;justify-content:center;margin-bottom:1.5rem;">
                <span class="cp-dot"></span>
                Available for new projects
            </div>
            <div class="cp-grid">

                <div class="cp-info" id="cpInfo">
                    <p class="cp-intro"><?= e(getSetting('contact_section_body', "Have a project in mind? Looking for a designer for your next publication, brand identity, or print campaign? I'd love to hear about it.")) ?></p>
                    <div class="cp-details">
                        <?php $email = getSetting('contact_email'); if ($email): ?>
                        <a href="mailto:<?= e($email) ?>" class="cp-detail">
                            <span class="cp-detail-icon"><i class="bi bi-envelope"></i></span>
                            <span class="cp-detail-text">
                                <span class="cp-detail-label">Email</span>
                                <span class="cp-detail-value"><?= e($email) ?></span>
                            </span>
                            <i class="bi bi-arrow-up-right cp-detail-arrow"></i>
                        </a>
                        <?php endif; ?>
                        <?php $phone = getSetting('contact_phone'); if ($phone): ?>
                        <a href="tel:<?= e(preg_replace('/\s+/', '', $phone)) ?>" class="cp-detail">
                            <span class="cp-detail-icon"><i class="bi bi-telephone"></i></span>
                            <span class="cp-detail-text">
                                <span class="cp-detail-label">Phone</span>
                                <span class="cp-detail-value"><?= e($phone) ?></span>
                            </span>
                            <i class="bi bi-arrow-up-right cp-detail-arrow"></i>
                        </a>
                        <?php endif; ?>
                        <?php $loc = getSetting('contact_location'); if ($loc): ?>
                        <?php $locUrl = getSetting('contact_location_url'); ?>
                        <?php if ($locUrl): ?><a href="<?= e($locUrl) ?>" target="_blank" rel="noopener" class="cp-detail"><?php else: ?><div class="cp-detail"><?php endif; ?>
                            <span class="cp-detail-icon"><i class="bi bi-geo-alt"></i></span>
                            <span class="cp-detail-text">
                                <span class="cp-detail-label">Location</span>
                                <span class="cp-detail-value"><?= e($loc) ?></span>
                            </span>
                            <?php if ($locUrl): ?><i class="bi bi-arrow-up-right cp-detail-arrow"></i><?php endif; ?>
                        <?php if ($locUrl): ?></a><?php else: ?></div><?php endif; ?>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="cp-form-wrap" id="cpForm">
                    <?php if ($contactErrors): ?>
                    <div class="cp-errors">
                        <?php foreach ($contactErrors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/#contact" id="contactForm" class="cp-form" <?= $contactSuccess ? 'data-sent="1"' : '' ?>>
                        <?= csrfField() ?>
                        <input type="hidden" name="contact_form" value="1">
                        <div class="cp-field">
                            <input type="text" id="cp_name" name="name" class="cp-input" value="" placeholder=" " required maxlength="100">
                            <label class="cp-label" for="cp_name">Your Name</label>
                            <span class="cp-line"></span>
                        </div>
                        <div class="cp-field">
                            <input type="email" id="cp_email" name="email" class="cp-input" value="" placeholder=" " required maxlength="150">
                            <label class="cp-label" for="cp_email">Email Address</label>
                            <span class="cp-line"></span>
                        </div>
                        <div class="cp-field">
                            <input type="text" id="cp_subject" name="subject" class="cp-input" value="" placeholder=" " maxlength="200">
                            <label class="cp-label" for="cp_subject">Subject <span style="opacity:0.4;font-size:0.85em;">(optional)</span></label>
                            <span class="cp-line"></span>
                        </div>
                        <div class="cp-field">
                            <textarea id="cp_message" name="message" class="cp-input cp-textarea" placeholder=" " required maxlength="5000"></textarea>
                            <label class="cp-label" for="cp_message">Message</label>
                            <span class="cp-line"></span>
                        </div>
                        <button type="submit" class="cp-submit">
                            <span>Send Message</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</section>

</div><!-- /.dark-end-block -->

<?php include __DIR__ . '/includes/footer.php'; ?>