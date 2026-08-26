<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Categories';

// Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    verifyCsrf();
    $name  = trim($_POST['name'] ?? '');
    $slug  = makeSlug(trim($_POST['slug'] ?? '') ?: $name);
    $order = (int)($_POST['display_order'] ?? 0);
    if ($name && $slug) {
        try {
            db()->prepare('INSERT INTO categories (name, slug, display_order) VALUES (?,?,?)')->execute([$name, $slug, $order]);
            flash('success', 'Category added.');
        } catch (PDOException $e) {
            flash('error', 'Slug already exists.');
        }
    }
    header('Location: ' . BASE_URL . '/admin/categories.php'); exit;
}

// Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    verifyCsrf();
    $cid   = (int)$_POST['cat_id'];
    $name  = trim($_POST['name'] ?? '');
    $slug  = makeSlug(trim($_POST['slug'] ?? '') ?: $name);
    $order = (int)($_POST['display_order'] ?? 0);
    if ($name && $slug) {
        try {
            db()->prepare('UPDATE categories SET name=?,slug=?,display_order=? WHERE id=?')->execute([$name, $slug, $order, $cid]);
            flash('success', 'Category updated.');
        } catch (PDOException $e) {
            flash('error', 'Slug already exists.');
        }
    }
    header('Location: ' . BASE_URL . '/admin/categories.php'); exit;
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
    verifyCsrf();
    $cid = (int)$_POST['cat_id'];
    // Nullify projects
    db()->prepare('UPDATE projects SET category_id = NULL WHERE category_id = ?')->execute([$cid]);
    db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$cid]);
    flash('success', 'Category deleted.');
    header('Location: ' . BASE_URL . '/admin/categories.php'); exit;
}

$categories = db()->query('SELECT c.*, COUNT(p.id) AS project_count FROM categories c LEFT JOIN projects p ON p.category_id = c.id GROUP BY c.id ORDER BY c.display_order ASC, c.name ASC')->fetchAll();
include __DIR__ . '/includes/header.php';
?>

<?= renderFlash() ?>

<div class="page-header">
    <h2>Categories</h2>
    <button class="btn-admin-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Add Category</button>
</div>

<div class="admin-card">
    <!-- Table: desktop -->
    <div class="d-none d-md-block">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Slug</th><th>Projects</th><th>Order</th><th>Actions</th></tr></thead>
        <tbody id="sortableList" data-endpoint="reorder-categories.php">
        <?php foreach ($categories as $cat): ?>
        <tr data-id="<?= $cat['id'] ?>">
            <td><i class="bi bi-grip-vertical order-handle me-2"></i><?= e($cat['name']) ?></td>
            <td style="color:var(--text-muted);font-size:0.82rem"><?= e($cat['slug']) ?></td>
            <td style="color:var(--text-muted);font-size:0.82rem"><?= $cat['project_count'] ?></td>
            <td style="color:var(--text-muted);font-size:0.82rem"><?= $cat['display_order'] ?></td>
            <td>
                <button class="btn-icon" onclick="openEditModal(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>', '<?= e(addslashes($cat['slug'])) ?>', <?= $cat['display_order'] ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
                <form method="POST" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                    <button type="submit" name="delete_category" class="btn-icon danger"
                        data-confirm="Delete category '<?= e(addslashes($cat['name'])) ?>'? Projects in this category will become uncategorized."
                        title="Delete"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?>
        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:2rem">No categories yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- Cards: mobile -->
    <div class="d-md-none">
        <?php if (empty($categories)): ?>
        <p style="text-align:center;color:var(--text-muted);padding:2rem 0">No categories yet.</p>
        <?php endif; ?>
        <?php foreach ($categories as $cat): ?>
        <div class="proj-mobile-card">
            <i class="bi bi-grip-vertical order-handle" style="font-size:1.1rem;color:#444;flex-shrink:0"></i>
            <div class="proj-mobile-info">
                <div class="proj-mobile-title"><?= e($cat['name']) ?></div>
                <div class="proj-mobile-meta">
                    <span><?= e($cat['slug']) ?></span>
                    <span>&middot; <?= $cat['project_count'] ?> project<?= $cat['project_count'] != 1 ? 's' : '' ?></span>
                </div>
            </div>
            <div class="d-flex gap-1 align-items-center">
                <button class="btn-icon" onclick="openEditModal(<?= $cat['id'] ?>, '<?= e(addslashes($cat['name'])) ?>', '<?= e(addslashes($cat['slug'])) ?>', <?= $cat['display_order'] ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
                <form method="POST" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                    <button type="submit" name="delete_category" class="btn-icon danger"
                        data-confirm="Delete category '<?= e(addslashes($cat['name'])) ?>'? Projects in this category will become uncategorized."
                        title="Delete"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="background:var(--surface);border:1px solid var(--border)">
        <div class="modal-header" style="border-color:var(--border)">
            <h5 class="modal-title" style="font-size:0.95rem">Add Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1) opacity(0.5)"></button>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" id="addName" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" id="addSlug" class="form-control" placeholder="auto-generated"></div>
                <div class="mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" class="form-control" value="0" min="0"></div>
            </div>
            <div class="modal-footer" style="border-color:var(--border)">
                <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_category" class="btn-admin-primary">Add</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content" style="background:var(--surface);border:1px solid var(--border)">
        <div class="modal-header" style="border-color:var(--border)">
            <h5 class="modal-title" style="font-size:0.95rem">Edit Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1) opacity(0.5)"></button>
        </div>
        <form method="POST">
            <?= csrfField() ?>
            <input type="hidden" name="cat_id" id="editId">
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" id="editName" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Slug</label><input type="text" name="slug" id="editSlug" class="form-control"></div>
                <div class="mb-3"><label class="form-label">Display Order</label><input type="number" name="display_order" id="editOrder" class="form-control" min="0"></div>
            </div>
            <div class="modal-footer" style="border-color:var(--border)">
                <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="edit_category" class="btn-admin-primary">Save</button>
            </div>
        </form>
    </div></div>
</div>

<script>
// Auto-slug in add modal
document.getElementById('addName')?.addEventListener('input', function() {
    document.getElementById('addSlug').value = this.value.toLowerCase().replace(/[^a-z0-9\s-]/g,'').replace(/[\s-]+/g,'-').replace(/^-+|-+$/g,'');
});
function openEditModal(id, name, slug, order) {
    document.getElementById('editId').value    = id;
    document.getElementById('editName').value  = name;
    document.getElementById('editSlug').value  = slug;
    document.getElementById('editOrder').value = order;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
