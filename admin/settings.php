<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Settings';

// ── Site settings ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    verifyCsrf();
    $keys = ['site_title','site_description','designer_title','contact_email','contact_phone','contact_location','contact_location_url',
             'social_instagram','social_linkedin','social_behance','social_twitter',
             'social_telegram','social_facebook','social_tiktok','social_youtube','copyright_text'];
    foreach ($keys as $key) {
        $val = trim($_POST[$key] ?? '');
        db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$key, $val, $val]);
    }
    if (!empty($_FILES['favicon']['name'])) {
        $result = saveUpload($_FILES['favicon'], 'profile');
        if ($result) db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('favicon',?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$result['path'], $result['path']]);
    }
    flash('success', 'Settings saved.');
    header('Location: ' . BASE_URL . '/admin/settings.php'); exit;
}

// ── SMTP settings ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_smtp'])) {
    verifyCsrf();
    $smtpKeys = ['smtp_host','smtp_port','smtp_username','smtp_encryption','smtp_from_name','smtp_from_email','contact_email'];
    foreach ($smtpKeys as $key) {
        $val = trim($_POST[$key] ?? '');
        db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$key, $val, $val]);
    }
    if (($_POST['smtp_password'] ?? '') !== '') {
        $p = $_POST['smtp_password'];
        db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('smtp_password',?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$p, $p]);
    }
    $enabled = isset($_POST['smtp_enabled']) ? '1' : '0';
    db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('smtp_enabled',?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$enabled, $enabled]);
    flash('success', 'Email settings saved.');
    header('Location: ' . BASE_URL . '/admin/settings.php#smtp'); exit;
}

// ── Account credentials ───────────────────────────────────
$accountErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_account'])) {
    verifyCsrf();
    $currentPassword = $_POST['current_password'] ?? '';
    $newEmail        = trim($_POST['new_email'] ?? '');
    $newPassword     = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $user = db()->prepare('SELECT * FROM users WHERE id = ?');
    $user->execute([$_SESSION['admin_id']]);
    $user = $user->fetch();

    if (!password_verify($currentPassword, $user['password'])) {
        $accountErrors[] = 'Current password is incorrect.';
    }
    if ($newEmail && $newEmail !== $user['email']) {
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $accountErrors[] = 'New email address is not valid.';
        } else {
            $taken = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $taken->execute([$newEmail, $user['id']]);
            if ($taken->fetch()) $accountErrors[] = 'That email is already in use.';
        }
    }
    if ($newPassword !== '') {
        if (strlen($newPassword) < 8) $accountErrors[] = 'New password must be at least 8 characters.';
        elseif ($newPassword !== $confirmPassword) $accountErrors[] = 'New passwords do not match.';
    }
    if (empty($accountErrors)) {
        $updateEmail    = $newEmail ?: $user['email'];
        $updatePassword = $newPassword !== '' ? password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]) : $user['password'];
        db()->prepare('UPDATE users SET email = ?, password = ? WHERE id = ?')->execute([$updateEmail, $updatePassword, $user['id']]);
        flash('success', 'Account credentials updated.');
        header('Location: ' . BASE_URL . '/admin/settings.php'); exit;
    }
}

$s = getAllSettings();
$currentUser = db()->prepare('SELECT name, email FROM users WHERE id = ?');
$currentUser->execute([$_SESSION['admin_id']]);
$currentUser = $currentUser->fetch();

include __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<div class="page-header"><h2>Site Settings</h2></div>

<!-- ── Site Settings ──────────────────────────────────────── -->
<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="admin-card mb-3">
                <div class="admin-card-header"><span class="admin-card-title">General</span></div>
                <div class="mb-3"><label class="form-label">Site Title <span class="form-text d-inline">(your name / studio name — used in nav &amp; footer)</span></label><input type="text" name="site_title" class="form-control" value="<?= e($s['site_title'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label">Designer Title <span class="form-text d-inline">(e.g. Graphic Designer — shown under name in nav, footer &amp; hero)</span></label><input type="text" name="designer_title" class="form-control" value="<?= e($s['designer_title'] ?? 'Graphic Designer') ?>"></div>
                <div class="mb-3"><label class="form-label">Site Description</label><textarea name="site_description" class="form-control" rows="3"><?= e($s['site_description'] ?? '') ?></textarea></div>
                <div class="mb-3"><label class="form-label">Copyright Text</label><input type="text" name="copyright_text" class="form-control" value="<?= e($s['copyright_text'] ?? '') ?>"></div>
            </div>
            <div class="admin-card mb-3">
                <div class="admin-card-header"><span class="admin-card-title">Contact Info</span></div>
                <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="contact_phone" class="form-control" value="<?= e($s['contact_phone'] ?? '') ?>"></div>
                <div class="mb-3">
                    <label class="form-label">Location <span class="form-text d-inline">(display text)</span></label>
                    <input type="text" name="contact_location" class="form-control" value="<?= e($s['contact_location'] ?? '') ?>" placeholder="e.g. Addis Ababa, Ethiopia">
                </div>
                <div class="mb-3">
                    <label class="form-label">Google Maps URL</label>
                    <input type="url" name="contact_location_url" class="form-control" value="<?= e($s['contact_location_url'] ?? '') ?>" placeholder="https://maps.google.com/?q=...">
                    <div class="form-text">Open <a href="https://maps.google.com" target="_blank">Google Maps</a>, search your location, click <strong>Share → Copy link</strong>, paste here.</div>
                </div>
            </div>
            <div class="admin-card">
                <div class="admin-card-header"><span class="admin-card-title">Social Links</span></div>
                <div class="mb-3"><label class="form-label"><i class="bi bi-instagram me-1"></i>Instagram URL</label><input type="url" name="social_instagram" class="form-control" value="<?= e($s['social_instagram'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label"><i class="bi bi-linkedin me-1"></i>LinkedIn URL</label><input type="url" name="social_linkedin" class="form-control" value="<?= e($s['social_linkedin'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label"><i class="bi bi-behance me-1"></i>Behance URL</label><input type="url" name="social_behance" class="form-control" value="<?= e($s['social_behance'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label"><i class="bi bi-twitter-x me-1"></i>Twitter/X URL</label><input type="url" name="social_twitter" class="form-control" value="<?= e($s['social_twitter'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label"><i class="bi bi-telegram me-1"></i>Telegram URL</label><input type="url" name="social_telegram" class="form-control" value="<?= e($s['social_telegram'] ?? '') ?>" placeholder="https://t.me/username"></div>
                <div class="mb-3"><label class="form-label"><i class="bi bi-facebook me-1"></i>Facebook URL</label><input type="url" name="social_facebook" class="form-control" value="<?= e($s['social_facebook'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label"><i class="bi bi-tiktok me-1"></i>TikTok URL</label><input type="url" name="social_tiktok" class="form-control" value="<?= e($s['social_tiktok'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label"><i class="bi bi-youtube me-1"></i>YouTube URL</label><input type="url" name="social_youtube" class="form-control" value="<?= e($s['social_youtube'] ?? '') ?>"></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="admin-card">
                <div class="admin-card-header"><span class="admin-card-title">Favicon</span></div>
                <?php if (!empty($s['favicon'])): ?>
                <img src="<?= e(assetUrl($s['favicon'])) ?>" style="width:48px;height:48px;object-fit:contain;margin-bottom:0.75rem;display:block" alt="">
                <?php endif; ?>
                <input type="file" name="favicon" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <div class="form-text mt-1">PNG recommended</div>
            </div>
        </div>
    </div>
    <div class="mt-3 mb-5">
        <button type="submit" name="save_settings" class="btn-admin-primary"><i class="bi bi-check-lg"></i> Save Settings</button>
    </div>
</form>

<!-- ── Email / SMTP Settings ──────────────────────────────── -->
<div class="admin-card mb-4" id="smtp">
    <div class="admin-card-header">
        <span class="admin-card-title">Email &amp; SMTP Settings</span>
        <?php if (($s['smtp_enabled'] ?? '0') === '1'): ?>
        <span style="font-size:0.78rem;color:var(--success)"><i class="bi bi-check-circle"></i> SMTP Active</span>
        <?php else: ?>
        <span style="font-size:0.78rem;color:var(--text-muted)"><i class="bi bi-exclamation-circle"></i> Using PHP mail() — may land in spam</span>
        <?php endif; ?>
    </div>

    <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1.5rem;line-height:1.8;padding:0.85rem 1rem;background:var(--surface2);border-radius:6px;border-left:2px solid #444;">
        <strong style="color:var(--accent-dim)">Gmail setup:</strong>
        Host <code style="color:#ccc">smtp.gmail.com</code> &nbsp;·&nbsp; Port <code style="color:#ccc">587</code> &nbsp;·&nbsp; Encryption <code style="color:#ccc">TLS</code><br>
        Username = your Gmail address &nbsp;·&nbsp;
        Password = <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color:#aaa;text-decoration:underline">App Password</a> (not your Gmail login password — requires 2FA enabled)<br>
        <strong style="color:var(--accent-dim)">cPanel hosting:</strong> use the SMTP details from cPanel &rarr; Email Accounts &rarr; Connect Devices.
    </div>

    <form method="POST">
        <?= csrfField() ?>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label">Receiving Email <span style="color:#e05555">*</span></label>
                <input type="email" name="contact_email" class="form-control"
                    value="<?= e($s['contact_email'] ?? '') ?>"
                    placeholder="your@email.com" required>
                <div class="form-text">Contact form messages are delivered to this address.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Name</label>
                <input type="text" name="smtp_from_name" class="form-control"
                    value="<?= e($s['smtp_from_name'] ?? 'Amanuel Yohannes') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">From Email</label>
                <input type="email" name="smtp_from_email" class="form-control"
                    value="<?= e($s['smtp_from_email'] ?? '') ?>"
                    placeholder="noreply@yourdomain.com">
            </div>
        </div>

        <hr class="divider">

        <div class="form-check mb-3">
            <input type="checkbox" name="smtp_enabled" id="smtpEnabled" class="form-check-input"
                <?= ($s['smtp_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
                onchange="document.getElementById('smtpFields').style.display=this.checked?'block':'none'">
            <label for="smtpEnabled" class="form-check-label">Enable SMTP <span style="color:var(--text-muted);font-size:0.8rem">(recommended — required for Gmail)</span></label>
        </div>

        <div id="smtpFields" style="display:<?= ($s['smtp_enabled'] ?? '0') === '1' ? 'block' : 'none' ?>">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="smtp_host" class="form-control"
                        value="<?= e($s['smtp_host'] ?? '') ?>"
                        placeholder="smtp.gmail.com">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Port</label>
                    <input type="number" name="smtp_port" class="form-control"
                        value="<?= e($s['smtp_port'] ?? '587') ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Encryption</label>
                    <select name="smtp_encryption" class="form-select">
                        <option value="tls"  <?= ($s['smtp_encryption'] ?? 'tls') === 'tls'  ? 'selected' : '' ?>>TLS — port 587 (recommended)</option>
                        <option value="ssl"  <?= ($s['smtp_encryption'] ?? '') === 'ssl'  ? 'selected' : '' ?>>SSL — port 465</option>
                        <option value="none" <?= ($s['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None — port 25</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">SMTP Username</label>
                    <input type="text" name="smtp_username" class="form-control"
                        value="<?= e($s['smtp_username'] ?? '') ?>"
                        placeholder="your@gmail.com" autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label">SMTP Password</label>
                    <input type="password" name="smtp_password" class="form-control"
                        placeholder="<?= !empty($s['smtp_password']) ? 'Saved — leave blank to keep unchanged' : 'App password or SMTP password' ?>"
                        autocomplete="new-password">
                    <?php if (!empty($s['smtp_password'])): ?>
                    <div class="form-text">A password is saved. Leave blank to keep it.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button type="submit" name="save_smtp" class="btn-admin-primary">
                <i class="bi bi-envelope-check"></i> Save Email Settings
            </button>
        </div>
    </form>
</div>

<!-- ── Account Credentials ────────────────────────────────── -->
<div class="admin-card">
    <div class="admin-card-header">
        <span class="admin-card-title">Account Credentials</span>
        <span style="font-size:0.78rem;color:var(--text-muted)">Signed in as <?= e($currentUser['email']) ?></span>
    </div>

    <?php if (!empty($accountErrors)): ?>
    <div class="alert alert-danger mb-3"><?= implode('<br>', array_map('e', $accountErrors)) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
        <?= csrfField() ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Current Password <span style="color:#e05555">*</span></label>
                <input type="password" name="current_password" class="form-control" required autocomplete="current-password" placeholder="Required to make any changes">
            </div>
        </div>
        <hr class="divider">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">New Email Address</label>
                <input type="email" name="new_email" class="form-control" value="<?= e($currentUser['email']) ?>" autocomplete="off">
                <div class="form-text">Leave unchanged to keep current email.</div>
            </div>
        </div>
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" class="form-control" autocomplete="new-password" placeholder="Leave blank to keep current password" minlength="8">
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" autocomplete="new-password" placeholder="Repeat new password">
            </div>
        </div>
        <div class="form-text mt-1 mb-3">Minimum 8 characters. Leave both password fields blank to change only the email.</div>
        <button type="submit" name="save_account" class="btn-admin-primary">
            <i class="bi bi-shield-lock"></i> Update Credentials
        </button>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
