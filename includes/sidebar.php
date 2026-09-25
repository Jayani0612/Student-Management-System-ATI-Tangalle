<!-- Sidebar - Mini Icon Version -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <div class="brand-text">
            <span>ATI</span>
        </div>
    </div>
    
    <div class="sidebar-menu-wrapper" id="sidebarMenuWrapper">
        <ul class="sidebar-menu">
            <!-- Dashboard -->
            <li>
                <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" title="Dashboard">
                    <i class="fas fa-th-large"></i>
                    <span class="menu-label">Dashboard</span>
                </a>
            </li>
            
            <!-- Academic Section -->
            <li class="menu-section">
                <span class="section-label">Academic</span>
            </li>
            <li>
                <a href="students.php" class="<?= basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : '' ?>" title="Students">
                    <i class="fas fa-users"></i>
                    <span class="menu-label">Students</span>
                </a>
            </li>
            <li>
                <a href="teachers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'active' : '' ?>" title="Teachers">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span class="menu-label">Teachers</span>
                </a>
            </li>
            <li>
                <a href="courses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : '' ?>" title="Courses">
                    <i class="fas fa-book"></i>
                    <span class="menu-label">Courses</span>
                </a>
            </li>
            <li>
                <a href="attendance.php" class="<?= basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : '' ?>" title="Attendance">
                    <i class="fas fa-clipboard-check"></i>
                    <span class="menu-label">Attendance</span>
                </a>
            </li>
            <li>
                <a href="timetable.php" class="<?= basename($_SERVER['PHP_SELF']) == 'timetable.php' ? 'active' : '' ?>" title="Timetable">
                    <i class="fas fa-clock"></i>
                    <span class="menu-label">Timetable</span>
                </a>
            </li>
            
            <!-- Administration Section -->
            <li class="menu-section">
                <span class="section-label">Admin</span>
            </li>
            <li>
                <a href="finance.php" class="<?= basename($_SERVER['PHP_SELF']) == 'finance.php' ? 'active' : '' ?>" title="Finance">
                    <i class="fas fa-coins"></i>
                    <span class="menu-label">Finance</span>
                </a>
            </li>
           
            <li>
                <a href="library.php" class="<?= basename($_SERVER['PHP_SELF']) == 'library.php' ? 'active' : '' ?>" title="Library">
                    <i class="fas fa-book-open"></i>
                    <span class="menu-label">Library</span>
                </a>
            </li>
            <li>
                <a href="transport.php" class="<?= basename($_SERVER['PHP_SELF']) == 'transport.php' ? 'active' : '' ?>" title="Transport">
                    <i class="fas fa-bus"></i>
                    <span class="menu-label">Transport</span>
                </a>
            </li>
            
            <!-- Other Section -->
            <li class="menu-section">
                <span class="section-label">Other</span>
            </li>
            <li>
                <a href="events.php" class="<?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : '' ?>" title="Events">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="menu-label">Events</span>
                </a>
            </li>
            <li>
                <a href="exams.php" class="<?= basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : '' ?>" title="Exams">
                    <i class="fas fa-pencil-alt"></i>
                    <span class="menu-label">Exams</span>
                    <span class="badge">New</span>
                </a>
            </li>
            <li>
                <a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" title="Reports">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-label">Reports</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" title="Settings">
                    <i class="fas fa-cog"></i>
                    <span class="menu-label">Settings</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
/* Keep sidebar menu scrollable and remember scroll position */
.sidebar-menu-wrapper {
    overflow-y: auto;
    overflow-x: hidden;
    scroll-behavior: auto; /* important: don't let 'smooth' fight the restore */
    height: calc(100vh - 70px); /* adjust 70px to match your sidebar-brand height */
}

/* Optional: nicer thin scrollbar */
.sidebar-menu-wrapper::-webkit-scrollbar {
    width: 5px;
}
.sidebar-menu-wrapper::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var menuWrapper = document.getElementById('sidebarMenuWrapper');
    if (!menuWrapper) return;

    var storageKey = 'sidebarScrollPos';

    // Restore saved scroll position immediately (before user sees it jump)
    var savedPos = sessionStorage.getItem(storageKey);
    if (savedPos !== null) {
        menuWrapper.scrollTop = parseInt(savedPos, 10);
    }

    // Keep saving scroll position as user scrolls (covers manual scroll too)
    menuWrapper.addEventListener('scroll', function () {
        sessionStorage.setItem(storageKey, menuWrapper.scrollTop);
    });

    // Also save right before navigating away (clicking a link / reload)
    var links = menuWrapper.querySelectorAll('a');
    links.forEach(function (link) {
        link.addEventListener('click', function () {
            sessionStorage.setItem(storageKey, menuWrapper.scrollTop);
        });
    });

    window.addEventListener('beforeunload', function () {
        sessionStorage.setItem(storageKey, menuWrapper.scrollTop);
    });
});
</script>