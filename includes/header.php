<!-- Header -->
<div class="header">
    <div class="header-left">
        <button class="hamburger" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="header-title">
            <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
            <p>Welcome back! Here's what's happening today.</p>
        </div>
    </div>
    
    <div class="header-right">
        <button class="header-btn" title="Search">
            <i class="fas fa-search"></i>
        </button>
        <button class="header-btn" title="Notifications">
            <i class="fas fa-bell"></i>
            <span class="dot"></span>
        </button>
        <div class="header-user">
            <div class="avatar"><?= $initials ?? 'U' ?></div>
            <div>
                <div class="name"><?= $full_name ?? 'User' ?></div>
                <div class="role"><?= $role ?? 'Staff' ?></div>
            </div>
        </div>
    </div>
</div>