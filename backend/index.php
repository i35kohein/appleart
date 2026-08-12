<?php 
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    include 'views/login.php';
    exit;
}
include 'includes/header.php'; 
?>

<div id="app-root" class="min-h-screen antialiased" style="background: transparent;">
<!-- PREMIUM TOP BAR -->
<div class="topbar sticky top-0 z-40">
    <div class="topbar-brand">
        <img src="aalogo.png" alt="Apple Art Logo" style="height: 26px; width: 26px; object-fit: contain;">
        <span><strong>Apple Art</strong> - Student Management System</span>
    </div>
    <div class="topbar-time" id="live-clock">Loading time...</div>
    
    <div class="d-flex align-items-center gap-2">
        <!-- UI MODE TOGGLE: Premium (decorated) <-> Basic (completely plain) -->
        <button id="basic-ui-toggle" class="basic-ui-toggle-btn" onclick="toggleBasicUIMode()" title="Toggle Basic UI mode" type="button" style="display:flex; align-items:center; gap:6px; cursor:pointer; background:transparent; border:1px solid var(--separator); border-radius:6px; padding:5px 10px; font-size:12.5px; font-weight:600; color:var(--text-primary); transition:0.2s;">
            <span class="material-symbols-rounded" style="font-size:16px;">view_module</span>
            <span id="basic-ui-label">Basic</span>
        </button>
        <div id="topbar-view-controls" style="display: none;">
            <div class="segmented-control student-view-toggle m-0" role="tablist" aria-label="Student list view mode" style="padding: 2px;">
                <button class="segment-btn active" id="student-view-list-btn" onclick="switchStudentListView('list')" type="button" style="padding: 4px 10px; display:flex; align-items:center; gap:4px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">view_sidebar</span> List
                </button>
                <button class="segment-btn" id="student-view-dashboard-btn" onclick="switchStudentListView('dashboard')" type="button" style="padding: 4px 10px; display:flex; align-items:center; gap:4px;">
                    <span class="material-symbols-rounded" style="font-size: 16px;">dashboard</span> Dashboard
                </button>
            </div>
        </div>
        <div class="dropdown">
            <?php 
            $isMasterAdmin = ($_SESSION['user_role'] ?? '') === 'master_admin';
            $isAdmin = in_array($_SESSION['user_role'] ?? '', ['admin', 'master_admin']);
            $displayName = $isMasterAdmin ? 'Kyaw Zin Hein' : htmlspecialchars($_SESSION['user_name'] ?? 'User');
            ?>
            <button class="topbar-user" onclick="this.nextElementSibling.classList.toggle('show')" style="display: flex; align-items: center; gap: 10px; cursor: pointer; background: transparent; border: none; padding: 0;">
                <div style="display: flex; flex-direction: column; align-items: flex-end; line-height: 1.2;">
                    <span style="font-size: 14px; font-weight: 600; color: var(--text-primary);"><?php echo $displayName; ?></span>
                    <?php if ($isMasterAdmin): ?><span style="font-size: 11px; color: var(--system-blue); font-weight: 500;">Master Admin</span><?php endif; ?>
                </div>
                <img src="aalogo.png" alt="User avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover; background: #fff; padding: 2px;">
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius: 12px; border: 1px solid var(--separator); padding: 8px;">
                <?php if ($isAdmin): ?>
                <li>
                    <button class="dropdown-item d-flex align-items-center gap-2" onclick="switchViewMode('admin')" style="border-radius: 8px; font-size: 14px; font-weight: 500; padding: 8px 12px;">
                        <span class="material-symbols-rounded" style="font-size: 18px;">settings</span> Settings
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <?php endif; ?>
                <li>
                    <button class="dropdown-item text-danger d-flex align-items-center gap-2" onclick="logoutUser()" style="border-radius: 8px; font-size: 14px; font-weight: 500; padding: 8px 12px;">
                        <span class="material-symbols-rounded" style="font-size: 18px;">logout</span> Logout
                    </button>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.dropdown-menu');
    const button = document.querySelector('.topbar-user');
    if (dropdown && dropdown.classList.contains('show') && !button.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});
</script>

<script>
async function logoutUser() {
    try {
        await fetch('api/logout.php');
        window.location.reload();
    } catch(e) {}
}
</script>

<!-- MAIN APP BODY -->
<div class="app-body min-h-[calc(100vh-48px)]">
    
    <!-- Left Navigation Rail -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Detail Pane (Dynamic Stage Views) -->
    <div class="detail-pane">
        <?php include 'views/profile.php'; ?>
        <?php include 'views/rollcall.php'; ?>
        <?php include 'views/student-report.php'; ?>
        <?php include 'views/contacts.php'; ?>
        <?php include 'views/exam.php'; ?>
        <?php include 'views/calendar.php'; ?>
        <?php include 'views/today-screen.php'; ?>
        <?php include 'views/admin.php'; ?> <!-- NEW ADMIN TAB -->
    </div>

</div>

<?php include 'includes/modals.php'; ?>
<?php include 'includes/footer.php'; ?>

<div style="position: fixed; bottom: 8px; right: 12px; font-size: 11px; color: var(--text-secondary); z-index: 1000; pointer-events: none; opacity: 0.6; font-weight: 500;" id="dev-credit">
    Developer Kyaw Zin Hein
</div>

<style>
    [data-ui="basic"] #dev-credit { display: none !important; }
</style>

</div>
