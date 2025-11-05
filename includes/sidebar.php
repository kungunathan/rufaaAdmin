<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="logo">Rufaa</div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <span>User Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="referrals.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'referrals.php' ? 'active' : ''; ?>">
                <span>Referral Management</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="issues.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'issues.php' ? 'active' : ''; ?>">
                <span>Reported Issues</span>
            </a>
        </li>
        
        <li class="nav-item">
            <a href="profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <span>My Profile</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="logout.php" class="nav-link">
                <span>Log out</span>
            </a>
        </li>
    </ul>
</div>