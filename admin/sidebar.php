<!-- ================= SIDEBAR ================= -->

<aside class="sidebar">

    <div class="sidebar-header">

        <h2>
            <i class="fa-solid fa-user-shield"></i>
            ADMIN PANEL
        </h2>

    </div>

    <nav>

        <a href="dashboard.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>

        <a href="users.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Users</span>
        </a>

        <a href="projects.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-folder"></i>
            <span>Projects</span>
        </a>

        <a href="certificates.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'certificates.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-trophy"></i>
            <span>Certificates</span>
        </a>

        <a href="education.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'education.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-graduation-cap"></i>
            <span>Education</span>
        </a>

        <a href="contact.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-envelope"></i>
            <span>Contact</span>
        </a>

        <a href="skills.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'skills.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-code"></i>
            <span>Skills</span>
        </a>

        <a href="admin_profile.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'admin_profile.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-user"></i>
            <span>Admin Profile</span>
        </a>

        <a href="admin_management.php"
           class="<?= basename($_SERVER['PHP_SELF']) == 'admin_management.php' ? 'active' : '' ?>">
<i class="fa-solid fa-user-shield"></i>
            <span>Admin Management</span>
        </a>

        <a href="logout.php">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>

    </nav>

</aside>