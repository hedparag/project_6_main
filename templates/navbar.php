<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">Employee Management</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <?php if ($_SESSION['user_type'] == 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="add_employee.php">Add Employee</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_employees.php">Manage Employees</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
