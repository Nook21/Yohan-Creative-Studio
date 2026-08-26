<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/mailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$success  = false;
$errors   = [];
$formData = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $formData['name']    = trim($_POST['name']    ?? '');
    $formData['email']   = trim($_POST['email']   ?? '');
    $formData['subject'] = trim($_POST['subject'] ?? '');
    $formData['message'] = trim($_POST['message'] ?? '');

    if (strlen($formData['name']) < 2)                          $errors[] = 'Please enter your name.';
    if (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($formData['message']) < 10)                      $errors[] = 'Please enter a message (at least 10 characters).';

    if (!$errors) {
        $toEmail     = getSetting('contact_email');
        $fromName    = getSetting('smtp_from_name',  'Amanuel Yohannes Portfolio');
        $fromEmail   = getSetting('smtp_from_email', 'noreply@portfolio.local');
        $smtpEnabled = getSetting('smtp_enabled', '0') === '1';
        $subject     = $formData['subject'] ?: 'Portfolio Enquiry from ' . $formData['name'];

        // HTML email body
        $htmlBody = '
        <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;color:#222;">
            <div style="background:#0a0a0a;padding:24px 32px;">
                <p style="color:#fff;font-size:13px;letter-spacing:0.15em;text-transform:uppercase;margin:0;">
                    Amanuel Yohannes — Portfolio
                </p>
            </div>
            <div style="padding:32px;background:#fafafa;border:1px solid #e8e8e8;">
                <h2 style="font-size:20px;font-weight:400;margin:0 0 24px;">New Contact Message</h2>
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <tr><td style="padding:8px 0;color:#888;width:100px;">From</td>
                        <td style="padding:8px 0;font-weight:500;">' . htmlspecialchars($formData['name']) . '</td></tr>
                    <tr><td style="padding:8px 0;color:#888;">Email</td>
                        <td style="padding:8px 0;"><a href="mailto:' . htmlspecialchars($formData['email']) . '" style="color:#0a0a0a;">' . htmlspecialchars($formData['email']) . '</a></td></tr>
                    <tr><td style="padding:8px 0;color:#888;">Subject</td>
                        <td style="padding:8px 0;">' . htmlspecialchars($formData['subject'] ?: '—') . '</td></tr>
                </table>
                <hr style="border:none;border-top:1px solid #e8e8e8;margin:20px 0;">
                <p style="font-size:13px;color:#888;margin:0 0 8px;text-transform:uppercase;letter-spacing:0.1em;">Message</p>
                <p style="font-size:15px;line-height:1.7;white-space:pre-wrap;">' . htmlspecialchars($formData['message']) . '</p>
            </div>
            <div style="padding:16px 32px;background:#f0f0f0;font-size:12px;color:#aaa;">
                Sent from your portfolio contact form &nbsp;·&nbsp; Reply directly to this email to respond.
            </div>
        </div>';

        $plainBody  = "Name: {$formData['name']}\n";
        $plainBody .= "Email: {$formData['email']}\n";
        $plainBody .= "Subject: " . ($formData['subject'] ?: '—') . "\n\n";
        $plainBody .= "Message:\n{$formData['message']}";

        $sent = false;
        $mailError = '';

        if ($toEmail) {
            if ($smtpEnabled) {
                // ── PHPMailer via SMTP ────────────────────────────
                try {
                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host       = getSetting('smtp_host');
                    $mail->SMTPAuth   = true;
                    $mail->Username   = getSetting('smtp_username');
                    $mail->Password   = getSetting('smtp_password');
                    $enc = getSetting('smtp_encryption', 'tls');
                    $mail->SMTPSecure = $enc === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : ($enc === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : '');
                    $mail->Port       = (int) getSetting('smtp_port', '587');
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom($fromEmail, $fromName);
                    $mail->addAddress($toEmail);
                    $mail->addReplyTo($formData['email'], $formData['name']);

                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body    = $htmlBody;
                    $mail->AltBody = $plainBody;

                    $mail->send();
                    $sent = true;
                } catch (Exception $e) {
                    $mailError = $e->getMessage();
                    $sent = false;
                }
            } else {
                // ── Fallback: PHP mail() ──────────────────────────
                $headers  = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
                $headers .= "Reply-To: {$formData['email']}\r\n";
                $sent = mail($toEmail, $subject, $plainBody, $headers);
            }
        } else {
            // No receiving email configured — still show success to visitor
            $sent = true;
        }

        if ($sent) {
            $success  = true;
            $formData = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        } else {
            $errors[] = 'Sorry, your message could not be sent. Please try again or contact directly by email.';
            // Log error for admin (silent to visitor)
            error_log('Contact form mail error: ' . $mailError);
        }
    }
}

$pageTitle = 'Contact';
include __DIR__ . '/includes/header.php';
?>

<div class="cp-wrap">

    <!-- ── HERO HEADING ─────────────────────────────────── -->
    <div class="cp-hero" id="cpHero">
        <p class="cp-eyebrow">Get In Touch</p>
        <h1 class="cp-title">Let's Work<br><em>Together.</em></h1>
        <div class="cp-availability">
            <span class="cp-dot"></span>
            Available for new projects
        </div>
    </div>

    <!-- ── BODY GRID ─────────────────────────────────────── -->
    <div class="cp-grid">

        <!-- Left: info -->
        <div class="cp-info" id="cpInfo">

            <p class="cp-intro">Have a project in mind? Looking for a designer for your next publication, brand identity, or print campaign? I'd love to hear about it.</p>

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
                <?php if ($locUrl): ?>
                <a href="<?= e($locUrl) ?>" target="_blank" rel="noopener" class="cp-detail">
                <?php else: ?>
                <div class="cp-detail">
                <?php endif; ?>
                    <span class="cp-detail-icon"><i class="bi bi-geo-alt"></i></span>
                    <span class="cp-detail-text">
                        <span class="cp-detail-label">Location</span>
                        <span class="cp-detail-value"><?= e($loc) ?></span>
                    </span>
                    <?php if ($locUrl): ?>
                    <i class="bi bi-arrow-up-right cp-detail-arrow"></i>
                    <?php endif; ?>
                <?php if ($locUrl): ?></a><?php else: ?></div><?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="cp-socials">
                <?php $ig = getSetting('social_instagram'); if ($ig): ?>
                <a href="<?= e($ig) ?>" target="_blank" rel="noopener" class="cp-social"><i class="bi bi-instagram"></i></a>
                <?php endif; ?>
                <?php $li = getSetting('social_linkedin'); if ($li): ?>
                <a href="<?= e($li) ?>" target="_blank" rel="noopener" class="cp-social"><i class="bi bi-linkedin"></i></a>
                <?php endif; ?>
                <?php $be = getSetting('social_behance'); if ($be): ?>
                <a href="<?= e($be) ?>" target="_blank" rel="noopener" class="cp-social"><i class="bi bi-behance"></i></a>
                <?php endif; ?>
                <?php $tw = getSetting('social_twitter'); if ($tw): ?>
                <a href="<?= e($tw) ?>" target="_blank" rel="noopener" class="cp-social"><i class="bi bi-twitter-x"></i></a>
                <?php endif; ?>
                <?php $tg = getSetting('social_telegram'); if ($tg): ?>
                <a href="<?= e($tg) ?>" target="_blank" rel="noopener" class="cp-social"><i class="bi bi-telegram"></i></a>
                <?php endif; ?>
                <?php $fb = getSetting('social_facebook'); if ($fb): ?>
                <a href="<?= e($fb) ?>" target="_blank" rel="noopener" class="cp-social"><i class="bi bi-facebook"></i></a>
                <?php endif; ?>
                <?php $tt = getSetting('social_tiktok'); if ($tt): ?>
                <a href="<?= e($tt) ?>" target="_blank" rel="noopener" class="cp-social"><i class="bi bi-tiktok"></i></a>
                <?php endif; ?>
                <?php $yt = getSetting('social_youtube'); if ($yt): ?>
                <a href="<?= e($yt) ?>" target="_blank" rel="noopener" class="cp-social"><i class="bi bi-youtube"></i></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: form -->
        <div class="cp-form-wrap" id="cpForm">
            <?php if ($success): ?>
            <div class="cp-success">
                <div class="cp-success-icon"><i class="bi bi-check-lg"></i></div>
                <h3>Message Sent</h3>
                <p>Thank you for reaching out. I'll be in touch soon.</p>
                <a href="<?= BASE_URL ?>/" class="btn-outline" style="margin-top:1.5rem;">Back to Work <i class="bi bi-arrow-right"></i></a>
            </div>
            <?php else: ?>

            <?php if ($errors): ?>
            <div class="cp-errors">
                <?php foreach ($errors as $err): ?>
                <p><?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/contact" id="contactForm" class="cp-form">
                <?= csrfField() ?>
                <div class="cp-field">
                    <input type="text" id="name" name="name" class="cp-input"
                           value="<?= e($formData['name']) ?>" placeholder=" " required maxlength="100">
                    <label class="cp-label" for="name">Your Name</label>
                    <span class="cp-line"></span>
                </div>
                <div class="cp-field">
                    <input type="email" id="email" name="email" class="cp-input"
                           value="<?= e($formData['email']) ?>" placeholder=" " required maxlength="150">
                    <label class="cp-label" for="email">Email Address</label>
                    <span class="cp-line"></span>
                </div>
                <div class="cp-field">
                    <input type="text" id="subject" name="subject" class="cp-input"
                           value="<?= e($formData['subject']) ?>" placeholder=" " maxlength="200">
                    <label class="cp-label" for="subject">Subject <span style="opacity:0.4;font-size:0.85em;">(optional)</span></label>
                    <span class="cp-line"></span>
                </div>
                <div class="cp-field">
                    <textarea id="message" name="message" class="cp-input cp-textarea"
                              placeholder=" " required maxlength="5000"><?= e($formData['message']) ?></textarea>
                    <label class="cp-label" for="message">Message</label>
                    <span class="cp-line"></span>
                </div>
                <button type="submit" class="cp-submit">
                    <span>Send Message</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
