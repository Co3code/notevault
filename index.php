<?php
    session_start();
    // var_dump($_SESSION);
    require 'config/db.php';
    $pageTitle = "Your Notes";
    require 'includes/header.php';

    // Check if user is logged in
    if (! isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
    }

    // Set $user_id from session
    $user_id = $_SESSION['user_id'];

    // Get all notes for sidebar
    $stmt_all = $conn->prepare("SELECT * FROM notes WHERE user_id = ? AND deleted_at IS NULL ORDER BY id DESC");
    $stmt_all->bind_param("i", $user_id);
    $stmt_all->execute();
    $all_notes = $stmt_all->get_result();

    // Get selected note if any
    $selected_note = null;
    if (isset($_GET['note_id'])) {
    $note_id  = (int) $_GET['note_id'];
    $stmt_sel = $conn->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
    $stmt_sel->bind_param("ii", $note_id, $user_id);
    $stmt_sel->execute();
    $result        = $stmt_sel->get_result();
    $selected_note = $result->fetch_assoc();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">

    <style>
        /* =============================================
           CSS CUSTOM PROPERTIES
        ============================================= */
        :root {
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-btn: linear-gradient(45deg, #667eea, #764ba2);
            --gradient-warning: linear-gradient(45deg, #f093fb, #f5576c);
            --gradient-danger: linear-gradient(45deg, #ff6b6b, #ee5a24);
            --sidebar-width: 300px;
            --sidebar-bg: rgba(255, 255, 255, 0.95);
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-color: #333;
            --border-color: rgba(0, 0, 0, 0.1);
        }

        /* =============================================
           DARK MODE VARIABLES
        ============================================= */
        body.dark-mode {
            --sidebar-bg: rgba(44, 62, 80, 0.95);
            --card-bg: rgba(44, 62, 80, 0.95);
            --text-color: #ecf0f1;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        /* =============================================
           BODY & LAYOUT
        ============================================= */
        body {
            background: var(--gradient-primary);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden; /* Removes the horizontal/bottom scrollbar */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ecf0f1;
        }

        /* =============================================
           SIDEBAR
        ============================================= */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .logout-btn-container {
            padding: 1.5rem;
            border-top: 1px solid var(--border-color);
        }

        /* =============================================
           SIDEBAR LINKS
        ============================================= */
        .sidebar-link {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 0 25px 25px 0;
            margin: 0.25rem 0;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: var(--gradient-btn);
            color: white;
            transform: translateX(5px);
        }

        .note-link {
            padding-left: 2rem;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Dark mode text overrides */
        .sidebar.dark-mode .text-muted { color: #bdc3c7 !important; }

        /* =============================================
           SIDEBAR TOGGLE (Mobile)
        ============================================= */
        .sidebar-toggle {
            display: none;
        }

        /* =============================================
           MAIN CONTENT
        ============================================= */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        h1 {
            color: white;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
        }

        /* =============================================
           CARDS
        ============================================= */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background: var(--card-bg);
            backdrop-filter: blur(10px);
        }

        .card-body {
            padding: 1.5rem;
        }

        .dark-mode .card {
            color: #ecf0f1;
        }

        /* =============================================
           FORM CONTROLS
        ============================================= */
        .form-control {
            border-radius: 10px;
            border: none;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.9);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: #fff;
        }

        .dark-mode .form-control {
            background: rgba(52, 73, 94, 0.9);
            color: #ecf0f1;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .dark-mode .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        /* =============================================
           BUTTONS
        ============================================= */
        .btn {
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--gradient-btn);
            border: none;
        }

        .btn-warning {
            background: var(--gradient-warning);
            border: none;
            color: white;
        }

        .btn-danger {
            background: var(--gradient-danger);
            border: none;
        }

        /* =============================================
           DARK MODE TOGGLE BUTTON
        ============================================= */
        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            border: none;
            background: var(--gradient-btn);
            color: white;
            font-size: 1.5rem;
        }

        /* =============================================
           SUMMERNOTE DARK MODE
        ============================================= */
        body.dark-mode .note-editor {
            background-color: #34495e !important;
            color: #ecf0f1 !important;
            border: 1px solid #2c3e50 !important;
        }

        body.dark-mode .note-toolbar,
        body.dark-mode .note-statusbar {
            background-color: #2c3e50 !important;
        }

        body.dark-mode .note-editable {
            background-color: #34495e !important;
            color: #ecf0f1 !important;
        }

        /* =============================================
           RESPONSIVE (Mobile)
        ============================================= */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: var(--gradient-btn);
                color: white;
                border: none;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <small class="text-muted">
                Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </small>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php" class="sidebar-link">
                <i class="fas fa-plus-circle me-2"></i>Add New Note
            </a>

            <div class="mt-3">
                <small class="text-muted px-3">Your Notes:</small>

                <?php while ($note = mysqli_fetch_assoc($all_notes)): ?>
                    <a href="index.php?note_id=<?php echo $note['id']; ?>"
                       class="sidebar-link note-link <?php echo($selected_note && $selected_note['id'] == $note['id']) ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt me-2"></i>
                        <?php echo htmlspecialchars(substr($note['title'], 0, 25)); ?>
                        <?php echo strlen($note['title']) > 25 ? '...' : ''; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </nav>

        <div class="logout-btn-container mt-auto">
            <a href="auth/logout.php" class="btn btn-danger w-100">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="text-center mb-4"></h1>

        <?php if ($selected_note): ?>
            <!-- View/Edit Selected Note -->
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars($selected_note['title']); ?>
                    </h5>
                    <p class="card-text"><?php echo $selected_note['content']; ?></p>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="notes/edit_note.php?id=<?php echo $selected_note['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="notes/delete_note.php?id=<?php echo $selected_note['id']; ?>"
                           class="btn btn-danger"
                           onclick="return confirm('Are you sure you want to delete this note?')">
                            <i class="fas fa-trash me-1"></i>Delete
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Add New Note -->
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-plus-circle me-2"></i>Add New Note
                    </h5>
                    <form action="notes/save_note.php" method="POST">
                        <div class="mb-3">
                            <input type="text" name="title" class="form-control" placeholder="Note title" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="content" id="summernote" class="form-control" rows="8" placeholder="Write your note..." required></textarea>
                        </div>
                        <button class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Save Note
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Dark Mode Toggle Button -->
    <button class="btn dark-mode-toggle" onclick="toggleDarkMode()" id="darkModeBtn">
        <i class="fas fa-moon"></i>
    </button>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const isDark = localStorage.getItem('darkMode') === 'enabled';
            if (isDark) {
                document.body.classList.add('dark-mode');
                document.getElementById('sidebar').classList.add('dark-mode');
                document.getElementById('darkModeBtn').innerHTML = '<i class="fas fa-sun"></i>';
            }

            if ($('#summernote').length) {
                $('#summernote').summernote({ height: 250 });
            }
        });

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            document.getElementById('sidebar').classList.toggle('dark-mode');

            const isDark = document.body.classList.contains('dark-mode');
            document.getElementById('darkModeBtn').innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');

            if ($('#summernote').length) {
                const content = $('#summernote').summernote('code');
                $('#summernote').summernote('destroy');
                $('#summernote').summernote({ height: 250 });
                $('#summernote').summernote('code', content);
            }
        }
    </script>
</body>
</html>