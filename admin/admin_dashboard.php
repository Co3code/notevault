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
    <title>Admin Dashboard | Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 280px;
            --primary-hex: #6366f1;
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --bg-body: #f1f5f9;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: #334155;
            letter-spacing: -0.01em;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
            padding: 1.5rem;
            z-index: 1050;
            display: flex;
            flex-direction: column;
        }

        .brand-box {
            padding: 1rem;
            margin-bottom: 2rem;
            background: var(--primary-gradient);
            border-radius: 12px;
            color: white;
            text-align: center;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            transition: all 0.3s ease;
        }

        /* Navigation */
        .nav-pills .nav-link {
            color: #64748b;
            padding: 0.8rem 1rem;
            margin-bottom: 0.4rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .nav-pills .nav-link:hover {
            background: #f8fafc;
            color: var(--primary-hex);
        }

        .nav-pills .nav-link.active {
            background: #eff6ff;
            color: var(--primary-hex);
            font-weight: 600;
        }

        /* Modern Card Styling */
        .stat-card {
            border: none;
            border-radius: 20px;
            padding: 1.25rem;
            background: white;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover { transform: translateY(-5px); }

        .icon-box {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .bg-soft-primary { background: #e0e7ff; color: #4338ca; }
        .bg-soft-success { background: #dcfce7; color: #15803d; }
        .bg-soft-warning { background: #fef9c3; color: #a16207; }

        /* Tables */
        .table-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .table thead th {
            background: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #64748b;
            padding: 1rem;
            border: none;
        }

        .table tbody td { padding: 1.2rem 1rem; border-bottom: 1px solid #f1f5f9; }

        .search-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.6rem 1rem 0.6rem 2.8rem;
        }

        .search-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem; padding-top: 5rem; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<button class="sidebar-toggle d-lg-none btn btn-primary position-fixed m-3" style="z-index:1100" onclick="toggleSidebar()">
    <i class="fa-solid fa-bars"></i>
</button>

<div class="sidebar" id="sidebar">
    <div class="brand-box">
        <h5 class="mb-0 fw-bold"><i class="fa-solid fa-shield-halved me-2"></i>Admin Core</h5>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto" id="dashboardTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active w-100 text-start" data-bs-toggle="tab" data-bs-target="#users" type="button">
                <i class="fa-solid fa-users-viewfinder me-2"></i> User Directory
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="tab" data-bs-target="#notes" type="button">
                <i class="fa-solid fa-box-archive me-2"></i> Active Content
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link w-100 text-start" data-bs-toggle="tab" data-bs-target="#trash" type="button">
                <i class="fa-solid fa-eraser me-2"></i> Trash Bin
            </button>
        </li>
    </ul>

    <div class="mt-4 pt-4 border-top">
        <a href="../auth/logout.php" class="btn btn-light text-danger w-100 fw-bold" style="border-radius:12px">
            <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Sign Out
        </a>
    </div>
</div>

<div class="main-content">
    <div class="container-fluid">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2 class="fw-bold mb-1">Dashboard</h2>
                <p class="text-muted mb-0">Welcome back, Admin. Here's what's happening.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="badge bg-white text-dark shadow-sm p-2 px-3 border" style="border-radius:10px">
                    <i class="fa-regular fa-calendar me-2 text-primary"></i><?php echo date('D, M j, Y'); ?>
                </div>
            </div>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-primary border-0 shadow-sm alert-dismissible fade show rounded-4 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-bell-concierge me-3 fs-4"></i>
                    <span><?php echo $alert; ?></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="icon-box bg-soft-primary"><i class="fa-solid fa-user-group"></i></div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">System Users</p>
                    <h2 class="fw-bold mb-0"><?php echo $totalUsers; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="icon-box bg-soft-success"><i class="fa-solid fa-file-signature"></i></div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">Active Notes</p>
                    <h2 class="fw-bold mb-0"><?php echo $totalActiveNotes; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="icon-box bg-soft-warning"><i class="fa-solid fa-trash-can"></i></div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">Archived Items</p>
                    <h2 class="fw-bold mb-0"><?php echo $totalDeletedNotes; ?></h2>
                </div>
            </div>
        </div>

        <div class="tab-content pt-2">
            <div class="tab-pane fade show active" id="users">
                <div class="table-container">
                    <div class="row align-items-center mb-4">
                        <div class="col-sm-6"><h5 class="fw-bold mb-0">User Directory</h5></div>
                        <div class="col-sm-6 mt-3 mt-sm-0">
                            <div class="search-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" class="form-control search-box" id="userSearch" placeholder="Search by name or email...">
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle" id="usersTable">
                            <thead>
                                <tr>
                                    <th># ID</th>
                                    <th>Username</th>
                                    <th>Status</th>
                                    <th class="text-end">Management</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $userResult->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-muted">#<?php echo $user['id']; ?></td>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td>
                                            <span class="badge bg-soft-primary px-3 py-2 rounded-pill"><?php echo $user['role']; ?></span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-white btn-sm border"><i class="fa-solid fa-pen text-primary"></i></a>
                                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-white btn-sm border" onclick="return confirm('Delete this user?');"><i class="fa-solid fa-trash text-danger"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <nav><ul class="pagination pagination-sm justify-content-end mt-4" id="usersPagination"></ul></nav>
                </div>
            </div>
            
            </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
    // Your existing pagination script remains the same
</script>
</body>
</html>