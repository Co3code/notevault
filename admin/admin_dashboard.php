<?php
    session_start();
    include '../config/db.php';

    if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
    }

    $alert = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';

    // Fetch stats
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
    <title>Admin Panel | Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --bg-light: #f8fafc;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: #1e293b; }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            padding: 2rem 1rem;
            z-index: 100;
        }

        .main-content { margin-left: var(--sidebar-width); padding: 2rem; }

        .nav-pills .nav-link {
            color: #64748b;
            margin-bottom: 0.5rem;
            font-weight: 500;
            transition: all 0.3s;
            border-radius: 10px;
        }

        .nav-pills .nav-link.active {
            background: var(--primary-gradient);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        /* Card Styling */
        .stat-card {
            border: none;
            border-radius: 16px;
            transition: transform 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .stat-card:hover { transform: translateY(-5px); }

        .icon-shape {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .table-container {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .search-box {
            border-radius: 10px;
            padding-left: 2.5rem;
            border: 1px solid #e2e8f0;
        }

        .search-wrapper { position: relative; }
        .search-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        .badge-role { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; }
    </style>
</head>
<body>

<div class="sidebar d-none d-md-block">
    <div class="px-3 mb-4">
        <h4 class="fw-bold text-primary"><i class="fa-solid fa-bolt me-2"></i>AdminPro</h4>
    </div>
    <ul class="nav nav-pills flex-column" id="dashboardTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active w-100 text-start" data-bs-toggle="tab" data-bs-target="#users" type="button">
                <i class="fa-solid fa-users me-2"></i> Users
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="tab" data-bs-target="#notes" type="button">
                <i class="fa-solid fa-note-sticky me-2"></i> Active Notes
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="tab" data-bs-target="#trash" type="button">
                <i class="fa-solid fa-trash-can me-2"></i> Trash
            </button>
        </li>
    </ul>
    <hr class="my-4">
    <a href="../auth/logout.php" class="btn btn-outline-danger w-100 rounded-pill">
        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
    </a>
</div>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Overview</h2>
            <span class="text-muted"><?php echo date('l, F jS'); ?></span>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?php echo $alert; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape me-3"><i class="fa-solid fa-user-group"></i></div>
                        <div>
                            <p class="mb-0 opacity-75">Total Users</p>
                            <h3 class="mb-0 fw-bold"><?php echo $totalUsers; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape me-3"><i class="fa-solid fa-check-double"></i></div>
                        <div>
                            <p class="mb-0 opacity-75">Active Notes</p>
                            <h3 class="mb-0 fw-bold"><?php echo $totalActiveNotes; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-dark text-white">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-shape me-3"><i class="fa-solid fa-trash"></i></div>
                        <div>
                            <p class="mb-0 opacity-75">In Trash</p>
                            <h3 class="mb-0 fw-bold"><?php echo $totalDeletedNotes; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="users">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">User Management</h5>
                        <div class="search-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" class="form-control search-box" id="userSearch" placeholder="Filter users...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User Details</th>
                                    <th>Role</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $userResult->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted">#<?php echo $user['id']; ?></td>
                                        <td><span class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></span></td>
                                        <td><span class="badge bg-soft-primary text-primary border border-primary-subtle badge-role"><?php echo $user['role']; ?></span></td>
                                        <td class="text-end">
                                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-light border"><i class="fa-solid fa-pen"></i></a>
                                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-light text-danger border" onclick="return confirm('Confirm delete?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <nav><ul class="pagination pagination-sm justify-content-end mt-3" id="usersPagination"></ul></nav>
                </div>
            </div>

            <div class="tab-pane fade" id="notes">
                <div class="table-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">Note Archives</h5>
                        <div class="search-wrapper">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" class="form-control search-box" id="noteSearch" placeholder="Search title/content...">
                        </div>
                    </div>
                    <table class="table align-middle" id="notesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Content Snippet</th>
                                <th>Author</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($note = $notesResult->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($note['title']); ?></td>
                                    <td class="text-muted"><?php echo substr(htmlspecialchars($note['content']), 0, 50) . '...'; ?></td>
                                    <td><small class="text-secondary"><i class="fa-solid fa-user me-1"></i><?php echo htmlspecialchars($note['username']); ?></small></td>
                                    <td class="text-end">
                                        <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="btn btn-sm btn-light border">Edit</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <nav><ul class="pagination pagination-sm justify-content-end mt-3" id="notesPagination"></ul></nav>
                </div>
            </div>

            <div class="tab-pane fade" id="trash">
                 <div class="table-container border-start border-warning border-4">
                    <h5 class="fw-bold text-warning mb-4"><i class="fa-solid fa-dumpster me-2"></i>Recently Deleted</h5>
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
                                        <td class="text-muted"><?php echo $dnote['deleted_at']; ?></td>
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
// I've optimized your existing pagination logic to be more concise
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

setupTableSearchPagination('userSearch','usersTable','usersPagination', 8);
setupTableSearchPagination('noteSearch','notesTable','notesPagination', 8);
</script>
</body>
</html>