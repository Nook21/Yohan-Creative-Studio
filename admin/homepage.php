<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Homepage';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $keys = ['hero_title','hero_subtitle','hero_cta_text','show_clients','show_about','show_services','contact_section_heading','contact_section_subheading','contact_section_body','show_sponsors'];
    foreach ($keys as $key) {
        $val = isset($_POST[$key]) ? (in_array($key, ['show_clients','show_about','show_services','show_sponsors']) ? '1' : trim($_POST[$key])) : '0';
        db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$key, $val, $val]);
    }
    flash('success', 'Homepage settings saved.');
    header('Location: ' . BASE_URL . '/admin/homepage.php'); exit;
}

$s = getAllSettings();
$featuredProjects = db()->query('SELECT id, title, slug, cover_image, featured FROM projects WHERE status="published" ORDER BY display_order ASC')->fetchAll();
include __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<div class="page-header">
    <h2>Homepage</h2>
    <a href="<?= BASE_URL ?>/" target="_blank" class="btn-admin-secondary"><i class="bi bi-box-arrow-up-right"></i> View Homepage</a>
</div>

<form method="POST">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="admin-card mb-3">
                <div class="admin-card-header"><span class="admin-card-title">Hero Section</span></div>
                <div class="mb-3"><label class="form-label">Hero Title</label><input type="text" name="hero_title" class="form-control" value="<?= e($s['hero_title'] ?? '') ?>"></div>
                <div class="mb-3"><label class="form-label">Hero Subtitle</label><textarea name="hero_subtitle" class="form-control" rows="3"><?= e($s['hero_subtitle'] ?? '') ?></textarea></div>
                <div class="mb-3"><label class="form-label">CTA Button Text</label><input type="text" name="hero_cta_text" class="form-control" value="<?= e($s['hero_cta_text'] ?? 'View Work') ?>"></div>
            </div>

            <div class="admin-card mb-3">
                <div class="admin-card-header"><span class="admin-card-title">"Get In Touch" Section</span></div>
                <div class="mb-3">
                    <label class="form-label">Section Heading</label>
                    <input type="text" name="contact_section_heading" class="form-control" value="<?= e($s['contact_section_heading'] ?? 'Start a conversation.') ?>">
                    <div class="form-text">Displayed as the large heading. Use a period at the end for style.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sub-label</label>
                    <input type="text" name="contact_section_subheading" class="form-control" value="<?= e($s['contact_section_subheading'] ?? 'Get In Touch') ?>">
                    <div class="form-text">Small uppercase label above the heading.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Body Text</label>
                    <textarea name="contact_section_body" class="form-control" rows="3"><?= e($s['contact_section_body'] ?? "Have a project in mind? I'd love to hear about it. Send a message and I'll get back to you.") ?></textarea>
                </div>
            </div>

            <div class="admin-card mb-3">
                <div class="admin-card-header"><span class="admin-card-title">Section Visibility</span></div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="show_sponsors" id="showSponsors" class="form-check-input" <?= ($s['show_sponsors'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="showSponsors" class="form-check-label">Show Sponsors section</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="show_about" id="showAbout" class="form-check-input" <?= ($s['show_about'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="showAbout" class="form-check-label">Show About section</label>
                </div>
                <div class="form-check mb-2">
                    <input type="checkbox" name="show_services" id="showServices" class="form-check-input" <?= ($s['show_services'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="showServices" class="form-check-label">Show Services section</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="show_clients" id="showClients" class="form-check-input" <?= ($s['show_clients'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label for="showClients" class="form-check-label">Show Clients section</label>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card">
                <div class="admin-card-header"><span class="admin-card-title">Featured Projects</span></div>
                <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem">Mark projects as "Featured" in the project editor to show them on the homepage.</p>
                <?php foreach ($featuredProjects as $p): ?>
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;font-size:0.85rem">
                    <?php if ($p['featured']): ?>
                    <i class="bi bi-star-fill" style="color:#e0a030"></i>
                    <?php else: ?>
                    <i class="bi bi-star" style="color:#444"></i>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/admin/project-edit.php?id=<?= $p['id'] ?>" style="color:var(--text);text-decoration:none"><?= e($p['title']) ?></a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($featuredProjects)): ?>
                <p style="font-size:0.82rem;color:var(--text-muted)">No published projects yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mt-2">
        <button type="submit" class="btn-admin-primary"><i class="bi bi-check-lg"></i> Save Homepage Settings</button>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
