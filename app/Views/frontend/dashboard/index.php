<!-- My Children Section -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title">My Children</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($children)): ?>
            <div class="row">
                <?php foreach ($children as $child): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <img src="<?= $child['profile_photo'] ?>" 
                                     class="rounded-circle mb-3" 
                                     width="100" 
                                     height="100" 
                                     alt="<?= $child['full_name'] ?>">
                                <h5 class="card-title"><?= $child['full_name'] ?></h5>
                                <p class="card-text">
                                    <small class="text-muted">Reg No: <?= $child['reg_no'] ?></small><br>
                                    <strong>Class:</strong> <?= $child['class_section'] ?><br>
                                    <strong>Age:</strong> <?= $child['age'] ?> years
                                </p>
                                <a href="<?= base_url('student/profile/' . $child['student_id']) ?>" 
                                   class="btn btn-primary btn-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No children found associated with your account.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Logout Button in Navbar -->
<nav class="navbar">
    <!-- ... other nav items ... -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                <?= session('member_username') ?>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="<?= base_url('profile') ?>">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= base_url('auth/logout') ?>">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </li>
    </ul>
</nav>