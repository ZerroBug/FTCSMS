<aside class="sidebar" id="sidebar">

    <!-- Mobile Close -->
    <div class="d-flex justify-content-end mb-2 d-lg-none">
        <button id="closeSidebar" class="btn btn-sm btn-light">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- LOGO -->
    <div class="logo">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/config.php'; ?>
        <img src="<?= BASE_URL ?>assets/images/logo.png" alt="Logo" class="sidebar-logo" />
        <div>
            <p class="small">Fast • Reliable • Seamless</p>
        </div>
    </div>

    <!-- NAVIGATION -->
    <nav class="nav flex-column mb-3">

        <!-- DASHBOARD -->
        <a class="nav-link active p-3" href="<?= BASE_URL ?>pages/accounts_dashboard.php">
            <i class="fas fa-chart-line"></i> Accounts Dashboard
        </a>

        <!-- FEES -->
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            href="#feesDropdown">
            <span><i class="fas fa-file-invoice-dollar"></i> Fees</span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse ps-3" id="feesDropdown">
            <a class="nav-link" href="<?= BASE_URL ?>pages/fee_payments.php">Pay Fees</a>
            <a class="nav-link" href="<?= BASE_URL ?>pages/fee_configuration.php">Fee Configuration</a>
            <a class="nav-link" href="<?= BASE_URL ?>pages/view_fees_paid.php">View Fees Paid</a>
        </div>

        <!-- INVOICES -->
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            href="#invoiceDropdown">
            <span><i class="fas fa-receipt"></i> Invoices</span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse ps-3" id="invoiceDropdown">
            <a class="nav-link" href="<?= BASE_URL ?>pages/generate_invoice.php">Generate Invoice</a>
            <a class="nav-link" href="<?= BASE_URL ?>pages/manage_invoices.php">Manage Invoices</a>
        </div>

        <!-- EXPENSES -->
        <!-- <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            href="#expensesDropdown">
            <span><i class="fas fa-money-bill-wave"></i> Expenses</span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse ps-3" id="expensesDropdown">
            <a class="nav-link" href="<?= BASE_URL ?>accounts/add_expense.php">Record Expense</a>
            <a class="nav-link" href="<?= BASE_URL ?>accounts/expense_reports.php">Expense Reports</a>
        </div> -->

        <!-- REPORTS -->
        <a class="nav-link d-flex justify-content-between align-items-center" data-bs-toggle="collapse"
            href="#reportsDropdown">
            <span><i class="fas fa-chart-pie"></i> Reports</span>
            <i class="fas fa-chevron-down"></i>
        </a>
        <div class="collapse ps-3" id="reportsDropdown">
            <a class="nav-link" href="<?= BASE_URL ?>pages/daily_report.php">Daily Collection</a>
            <a class="nav-link" href="<?= BASE_URL ?>pages/monthly_report.php">Monthly Summary</a>
            <a class="nav-link" href="<?= BASE_URL ?>pages/fees_debt_list.php">Debtors List</a>
        </div>

    </nav>

    <!-- ROLE -->
    <small style="color:#c49a47">Accounts Officer</small>

    <!-- ACCOUNT / UTILITIES -->
    <nav class="nav flex-column mt-3">

        <a class="nav-link" href="<?= BASE_URL ?>pages/notifications.php">
            <i class="fas fa-bell"></i> Notifications
        </a>

        <a class="nav-link" href="<?= BASE_URL ?>pages/settings.php">
            <i class="fas fa-cog"></i> Settings
        </a>

        <!-- RESET PASSWORD -->
        <a class="nav-link" href="<?= BASE_URL ?>pages/reset_user_password.php">
            <i class="fas fa-key"></i> Reset Password
        </a>

        <!-- LOGOUT -->
        <a class="nav-link text-danger" href="<?= BASE_URL ?>handlers/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>

    </nav>

    <div style="height:40px"></div>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');

    document.getElementById('menuToggle')?.addEventListener('click', () => {
        sidebar.classList.add('show');
    });

    document.getElementById('closeSidebar')?.addEventListener('click', () => {
        sidebar.classList.remove('show');
    });

    document.querySelectorAll('.sidebar .collapse').forEach(collapse => {
        collapse.addEventListener('show.bs.collapse', e => {
            const icon = e.target.previousElementSibling.querySelector('.fa-chevron-down');
            if (icon) icon.style.transform = 'rotate(180deg)';
        });
        collapse.addEventListener('hide.bs.collapse', e => {
            const icon = e.target.previousElementSibling.querySelector('.fa-chevron-down');
            if (icon) icon.style.transform = 'rotate(0deg)';
        });
    });
});
</script>