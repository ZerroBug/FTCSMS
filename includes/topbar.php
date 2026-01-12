<header class="topbar shadow-sm bg-white px-3 py-2" id="topbar">
    <div class="container-fluid">
        <div class="row align-items-center">

            <!-- LEFT SIDE -->
            <div class="col d-flex align-items-center gap-3">
                <button id="menuToggle" class="btn btn-light btn-sm d-lg-none">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mb-0 fw-semibold">Dashboard</h5>
            </div>

            <!-- RIGHT SIDE -->
            <div class="col-auto d-flex align-items-center gap-3">

                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-light btn-sm position-relative" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $total_notifications ?? 0 ?>
                        </span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li class="dropdown-header fw-bold">Notifications</li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <?php if (!empty($notifications)): ?>
                        <?php foreach ($notifications as $note): ?>
                        <li>
                            <a class="dropdown-item" href="<?= htmlspecialchars($note['link']); ?>">
                                <i class="<?= htmlspecialchars($note['icon']); ?> me-2"></i>
                                <?= htmlspecialchars($note['message']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <li class="dropdown-item text-muted">No notifications</li>
                        <?php endif; ?>

                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <a class="dropdown-item small text-center" href="<?= BASE_URL ?>users/notifications.php">
                                View all
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- USER DROPDOWN -->
                <?php
                $userPhotoPath = $_SERVER['DOCUMENT_ROOT'] . '/fasttrack_mis/assets/uploads/users/' . ($user_photo ?? '');
                $photo = (!empty($user_photo) && file_exists($userPhotoPath))
                    ? BASE_URL . 'assets/uploads/users/' . $user_photo
                    : 'https://ui-avatars.com/api/?name=' . urlencode($user_name ?? 'User') . '&background=2e1b47&color=fff';
                ?>

                <div class="dropdown">
                    <button class="btn btn-light btn-sm d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                        <img src="<?= $photo ?>" class="rounded-circle border shadow-sm"
                            style="width:34px;height:34px;object-fit:cover;">
                        <span class="fw-semibold d-none d-md-inline">
                            <?= htmlspecialchars($user_name ?? 'User'); ?>
                        </span>
                        <i class="fas fa-chevron-down small text-muted"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end p-3 shadow user-profile-card">
                        <h6 class="fw-semibold mb-3">User Profile</h6>

                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= $photo ?>" class="rounded" style="width:70px;height:70px;">
                            <div class="ms-3">
                                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($user_name ?? 'User'); ?></h6>
                                <small class="text-muted d-block"></small>
                                <small class="text-muted d-flex align-items-center mt-1">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <?= htmlspecialchars($user_email ?? 'no-email@example.com'); ?>
                                </small>
                            </div>
                        </div>

                        <!-- LOGOUT -->
                        <form action="<?= BASE_URL ?>handlers/logout.php" method="POST">
                            <button type="submit" class="btn btn-danger btn-sm w-100 mb-3">
                                Sign Out
                            </button>
                        </form>

                        <hr>

                        <div class="list-group small">
                            <a href="<?= BASE_URL ?>users/profile.php"
                                class="list-group-item border-0 px-0 d-flex align-items-start gap-3">
                                <i class="fas fa-user text-info fs-5"></i>
                                <div>
                                    <strong>My Profile</strong>
                                    <div class="text-muted">Account settings</div>
                                </div>
                            </a>

                            <a href="<?= BASE_URL ?>users/activities.php"
                                class="list-group-item border-0 px-0 d-flex align-items-start gap-3">
                                <i class="fas fa-file-alt text-danger fs-5"></i>
                                <div>
                                    <strong>My Activities</strong>
                                    <div class="text-muted">User logs</div>
                                </div>
                            </a>

                            <a href="<?= BASE_URL ?>users/notifications.php"
                                class="list-group-item border-0 px-0 d-flex align-items-start gap-3">
                                <i class="fas fa-bell text-primary fs-5"></i>
                                <div>
                                    <strong>Notifications</strong>
                                    <div class="text-muted">Latest updates</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</header>