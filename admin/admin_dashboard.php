<?php
    session_start();
    include '../config/db.php';

    if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }

    $alert = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';

    // Fetch stats - Logic remains identical
    $totalUsers        = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
    $totalActiveNotes  = $conn->query("SELECT COUNT(*) AS count FROM notes WHERE deleted_at IS NULL")->fetch_assoc()['count'];
    $totalDeletedNotes = $conn->query("SELECT COUNT(*) AS count FROM notes WHERE deleted_at IS NOT NULL")->fetch_assoc()['count'];

    $userResult         = $conn->query("SELECT * FROM users");
    $notesResult        = $conn->query("SELECT notes.*, users.username FROM notes JOIN users ON notes.user_id = users.id WHERE notes.deleted_at IS NULL");
    $deletedNotesResult = $conn->query("SELECT notes.*, users.username FROM notes JOIN users ON notes.user_id = users.id WHERE notes.deleted_at IS NOT NULL");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 280px;
            --primary-hex: #6366f1;
            --bg-body: #f8fafc;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: #334155;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            padding: 1.5rem;
            z-index: 1050;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            transition: all 0.3s ease;
        }

        /* Sidebar Navigation */
        .nav-pills .nav-link {
            color: #64748b;
            padding: 0.8rem 1rem;
            margin-bottom: 0.4rem;
            font-weight: 500;
            border-radius: 12px;
        }

        .nav-pills .nav-link.active {
            background: #eff6ff;
            color: var(--primary-hex);
            font-weight: 600;
        }

        /* Stat Cards */
        .stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 1.5rem;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }
        
        .stat-card:hover { transform: translateY(-4px); }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .bg-soft-indigo { background: #e0e7ff; color: #4338ca; }
        .bg-soft-emerald { background: #dcfce7; color: #15803d; }
        .bg-soft-rose { background: #ffe4e6; color: #be123c; }

        /* Tables & UI */
        .table-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .search-box {
            background: #f1f5f9;
            border: none;
            border-radius: 10px;
            padding-left: 2.5rem;
        }

        .search-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1040;
            backdrop-filter: blur(4px);
        }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem; padding-top: 5rem; }
            .sidebar-overlay.show { display: block; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<button class="btn btn-white border shadow-sm d-lg-none position-fixed m-3" style="z-index:1100; border-radius: 10px;" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <div class="mb-4 px-2">
        <h5 class="fw-bold text-primary"><i class="fa-solid fa-bolt-lightning me-2"></i>AdminPanel</h5>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto" id="dashboardTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active w-100 text-start" data-bs-toggle="tab" data-bs-target="#users" type="button">
                <i class="fa-solid fa-user-shield me-2"></i> Users
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="tab" data-bs-target="#notes" type="button">
                <i class="fa-solid fa-book-open me-2"></i> Active Notes
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="tab" data-bs-target="#trash" type="button">
                <i class="fa-solid fa-trash-can me-2"></i> Trash Bin
            </button>
        </li>
    </ul>

    <div class="pt-4 border-top">
        <a href="../auth/logout.php" class="btn btn-outline-danger w-100 rounded-3 fw-bold">
            <i class="fa-solid fa-power-off me-2"></i> Logout
        </a>
    </div>
</div>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold mb-0">Overview</h2>
                <p class="text-muted mb-0">System management & statistics</p>
            </div>
            <div class="text-end d-none d-sm-block">
                <span class="badge bg-white text-muted border py-2 px-3 rounded-pill"><?php echo date('l, d F Y'); ?></span>
            </div>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-4 mb-4" role="alert">
                <i class="fa-solid fa-check-circle me-2"></i> <?php echo $alert; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-indigo"><i class="fa-solid fa-users"></i></div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">Total Users</p>
                    <h3 class="fw-bold mb-0"><?php echo $totalUsers; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-emerald"><i class="fa-solid fa-sticky-note"></i></div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">Active Notes</p>
                    <h3 class="fw-bold mb-0"><?php echo $totalActiveNotes; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="icon-box bg-soft-rose"><i class="fa-solid fa-box-archive"></i></div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">In Trash</p>
                    <h3 class="fw-bold mb-0"><?php echo $totalDeletedNotes; ?></h3>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="users">
                <div class="table-container">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                        <h5 class="fw-bold mb-0">User Management</h5>
                        <div class="search-wrapper position-relative">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" class="form-control search-box" id="userSearch" placeholder="Filter users...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">ID</th>
                                    <th class="border-0">User Details</th>
                                    <th class="border-0">Role</th>
                                    <th class="border-0 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $userResult->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted small">#<?php echo $user['id']; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><span class="badge bg-light text-dark border rounded-pill px-3"><?php echo $user['role']; ?></span></td>
                                        <td class="text-end">
                                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-light border"><i class="fa-solid fa-pencil text-primary"></i></a>
                                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Confirm delete?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <nav><ul class="pagination pagination-sm justify-content-end mt-4" id="usersPagination"></ul></nav>
                </div>
            </div>

            <div class="tab-pane fade" id="notes">
                <div class="table-container">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                        <h5 class="fw-bold mb-0">Note Archives</h5>
                        <div class="search-wrapper position-relative">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" class="form-control search-box" id="noteSearch" placeholder="Search notes...">
                        </div>
                    </div>
                    <table class="table align-middle" id="notesTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Title</th>
                                <th class="border-0">Preview</th>
                                <th class="border-0">Author</th>
                                <th class="border-0 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($note = $notesResult->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($note['title']); ?></td>
                                    <td class="text-muted small"><?php echo substr(htmlspecialchars($note['content']), 0, 40) . '...'; ?></td>
                                    <td><small class="text-secondary"><?php echo htmlspecialchars($note['username']); ?></small></td>
                                    <td class="text-end">
                                        <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="btn btn-sm btn-light border">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <nav><ul class="pagination pagination-sm justify-content-end mt-4" id="notesPagination"></ul></nav>
                </div>
            </div>

            <div class="tab-pane fade" id="trash">
                 <div class="table-container border-start border-danger border-4">
                    <h5 class="fw-bold text-danger mb-4"><i class="fa-solid fa-dumpster-fire me-2"></i>Recently Deleted</h5>
                    <div class="table-responsive">
                        <table class="table" id="trashTable">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Deleted On</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($dnote = $deletedNotesResult->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($dnote['title']); ?></td>
                                        <td class="text-muted small"><?php echo $dnote['deleted_at']; ?></td>
                                        <td class="text-end">
                                            <a href="restore_note.php?id=<?php echo $dnote['id']; ?>" class="btn btn-sm btn-success rounded-pill px-3">Restore</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Logic
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }

    // Pagination & Search Logic (Same as yours, just ensured IDs match)
    function setupTableSearchPagination(inputId, tableId, paginationId, rowsPerPage = 8) {
        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        const pagination = document.getElementById(paginationId);
        const tbody = table.querySelector('tbody');

        function render() {
            const filter = input.value.toLowerCase();
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const filteredRows = rows.filter(row => row.textContent.toLowerCase().includes(filter));

            rows.forEach(r => r.style.display = 'none');
            const pageCount = Math.ceil(filteredRows.length / rowsPerPage);
            pagination.innerHTML = '';

            for (let i = 1; i <= pageCount; i++) {
                const li = document.createElement('li');
                li.className = 'page-item';
                li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                li.onclick = (e) => { e.preventDefault(); showPage(i); };
                pagination.appendChild(li);
            }

            function showPage(page) {
                const start = (page - 1) * rowsPerPage;
                filteredRows.slice(start, start + rowsPerPage).forEach(r => r.style.display = '');
                Array.from(pagination.children).forEach((li, idx) => li.classList.toggle('active', idx === page - 1));
            }
            if (pageCount > 0) showPage(1);
        }
        input.addEventListener('keyup', render);
        render();
    }

    // Initialize logic
    setupTableSearchPagination('userSearch','usersTable','usersPagination', 8);
    setupTableSearchPagination('noteSearch','notesTable','notesPagination', 8);
</script>
</body>
</html>