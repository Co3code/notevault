<?php
    session_start();
    include '../config/db.php';

    // Protect page — only admins
    if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
    }

    // Handle alert messages
    $alert = '';
    if (isset($_GET['message'])) {
    $alert = htmlspecialchars($_GET['message']);
    }

    // Fetch dashboard stats
    $totalUsers        = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
    $totalActiveNotes  = $conn->query("SELECT COUNT(*) AS count FROM notes WHERE deleted_at IS NULL")->fetch_assoc()['count'];
    $totalDeletedNotes = $conn->query("SELECT COUNT(*) AS count FROM notes WHERE deleted_at IS NOT NULL")->fetch_assoc()['count'];

    // Fetch all users
    $userResult = $conn->query("SELECT * FROM users");

    // Fetch all active notes
    $notesResult = $conn->query("
    SELECT notes.*, users.username
    FROM notes
    JOIN users ON notes.user_id = users.id
    WHERE notes.deleted_at IS NULL
");

    // Fetch all soft-deleted notes
    $deletedNotesResult = $conn->query("
    SELECT notes.*, users.username
    FROM notes
    JOIN users ON notes.user_id = users.id
    WHERE notes.deleted_at IS NOT NULL
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.stats-card { min-width: 150px; margin-right: 10px; }
.table-wrapper { max-height: 400px; overflow-y: auto; }
.search-box { margin-bottom: 10px; }
.card-header a { text-decoration: none; }
</style>
</head>
<body>
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Admin Dashboard</h1>
        <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
    </div>

    <?php if ($alert): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $alert; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="d-flex mb-4">
        <div class="card stats-card text-white bg-primary">
            <div class="card-body">
                <h5>Total Users</h5>
                <h3><?php echo $totalUsers; ?></h3>
            </div>
        </div>
        <div class="card stats-card text-white bg-success">
            <div class="card-body">
                <h5>Active Notes</h5>
                <h3><?php echo $totalActiveNotes; ?></h3>
            </div>
        </div>
        <div class="card stats-card text-white bg-warning">
            <div class="card-body">
                <h5>Deleted Notes</h5>
                <h3><?php echo $totalDeletedNotes; ?></h3>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Users</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">Active Notes</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="trash-tab" data-bs-toggle="tab" data-bs-target="#trash" type="button" role="tab">Trash</button>
        </li>
    </ul>

    <div class="tab-content mt-3">

        <!-- Users Tab -->
        <div class="tab-pane fade show active" id="users" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <a class="btn btn-link" data-bs-toggle="collapse" href="#usersCollapse">Show / Hide Users Table</a>
                </div>
                <div class="collapse show" id="usersCollapse">
                    <div class="card-body">
                        <input type="text" class="form-control search-box" id="userSearch" placeholder="Search users by username...">
                        <div class="table-wrapper">
                            <table class="table table-bordered table-hover" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $userResult->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo $user['role']; ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <nav><ul class="pagination" id="usersPagination"></ul></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Notes Tab -->
        <div class="tab-pane fade" id="notes" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <a class="btn btn-link" data-bs-toggle="collapse" href="#notesCollapse">Show / Hide Active Notes Table</a>
                </div>
                <div class="collapse show" id="notesCollapse">
                    <div class="card-body">
                        <input type="text" class="form-control search-box" id="noteSearch" placeholder="Search notes by title or content...">
                        <div class="table-wrapper">
                            <table class="table table-bordered table-hover" id="notesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Content</th>
                                        <th>User</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($note = $notesResult->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $note['id']; ?></td>
                                            <td><?php echo htmlspecialchars($note['title']); ?></td>
                                            <td><?php echo htmlspecialchars($note['content']); ?></td>
                                            <td><?php echo htmlspecialchars($note['username']); ?></td>
                                            <td>
                                                <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                <a href="delete_note.php?id=<?php echo $note['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this note?');">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            <nav><ul class="pagination" id="notesPagination"></ul></nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trash Tab -->
        <div class="tab-pane fade" id="trash" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <a class="btn btn-link" data-bs-toggle="collapse" href="#trashCollapse">Show / Hide Deleted Notes</a>
                </div>
                <div class="collapse show" id="trashCollapse">
                    <div class="card-body">
                        <input type="text" class="form-control search-box" id="trashSearch" placeholder="Search deleted notes...">
                        <div class="table-wrapper">
                            <?php if ($deletedNotesResult->num_rows == 0): ?>
                                <p class="text-muted">No deleted notes.</p>
                            <?php else: ?>
                                <table class="table table-bordered table-warning table-hover" id="trashTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Content</th>
                                            <th>User</th>
                                            <th>Deleted At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($dnote = $deletedNotesResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo $dnote['id']; ?></td>
                                                <td><?php echo htmlspecialchars($dnote['title']); ?></td>
                                                <td><?php echo htmlspecialchars($dnote['content']); ?></td>
                                                <td><?php echo htmlspecialchars($dnote['username']); ?></td>
                                                <td><?php echo $dnote['deleted_at']; ?></td>
                                                <td>
                                                    <a href="restore_note.php?id=<?php echo $dnote['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Restore this note?');">Restore</a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                                <nav><ul class="pagination" id="trashPagination"></ul></nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Search & Pagination logic (same as your version)
function setupTableSearchPagination(inputId, tableId, paginationId, rowsPerPage = 5) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    const pagination = document.getElementById(paginationId);
    const tbody = table.getElementsByTagName('tbody')[0];

    function render() {
        const filter = input.value.toLowerCase();
        const rows = Array.from(tbody.getElementsByTagName('tr'))
            .filter(row => row.textContent.toLowerCase().includes(filter));

        tbody.querySelectorAll('tr').forEach(r => r.style.display = 'none');

        const pageCount = Math.ceil(rows.length / rowsPerPage);
        pagination.innerHTML = '';

        for (let i = 1; i <= pageCount; i++) {
            const li = document.createElement('li');
            li.classList.add('page-item');
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', e => {
                e.preventDefault();
                showPage(i);
            });
            pagination.appendChild(li);
        }

        function showPage(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;
            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? '' : 'none';
            });
            Array.from(pagination.children).forEach(li => li.classList.remove('active'));
            if (pagination.children[page - 1]) pagination.children[page - 1].classList.add('active');
        }

        if (pageCount > 0) showPage(1);
    }

    input.addEventListener('keyup', render);
    render();
}

// Apply to all tables
setupTableSearchPagination('userSearch','usersTable','usersPagination',5);
setupTableSearchPagination('noteSearch','notesTable','notesPagination',5);
setupTableSearchPagination('trashSearch','trashTable','trashPagination',5);
</script>
 <!-- 
Search Boxes – Each tab (Users, Active Notes, Trash) has its own input at the top to filter rows in real-time.

Pagination – Each table now shows 5 rows per page by default and adds page navigation below the table. Pagination works together with the search, so filtered results are paginated dynamically.

Stats Cards – Total users, active notes, and deleted notes are displayed at the top.

Tabs + Collapsible Trash – Users can switch between tabs, and deleted notes can be collapsed/expanded.

Logout Button – At the top right, linking to your existing logout.php.

Alerts – Success messages show temporarily at the top.

Tip: If your tables grow really large, consider fetching users/notes with server-side pagination instead of loading everything at once. But for moderate amounts of data, this client-side approach is perfect.-->
</body>
</html>
 