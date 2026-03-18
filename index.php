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
        :root {
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-btn: linear-gradient(45deg, #667eea, #764ba2);
            --gradient-warning: linear-gradient(45deg, #f093fb, #f5576c);
            --gradient-danger: linear-gradient(45deg, #ff6b6b, #ee5a24);
            --sidebar-width: 280px;
            --sidebar-bg: rgba(255, 255, 255, 0.97);
            --card-bg: rgba(255, 255, 255, 0.97);
            --text-color: #2d3748;
            --text-muted: #718096;
            --border-color: rgba(0, 0, 0, 0.08);
        }

        body.dark-mode {
            --sidebar-bg: rgba(26, 32, 44, 0.97);
            --card-bg: rgba(26, 32, 44, 0.97);
            --text-color: #e2e8f0;
            --text-muted: #a0aec0;
            --border-color: rgba(255, 255, 255, 0.08);
        }

        body {
            background: var(--gradient-primary);
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            overflow-x: hidden;
            transition: background 0.3s ease, color 0.3s ease;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #1a202c 0%, #2d3748 100%);
            color: var(--text-color);
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            backdrop-filter: blur(20px);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 1.75rem 1.5rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.2rem;
            font-weight: 700;
            background: var(--gradient-btn);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.4rem;
        }

        .sidebar-brand i {
            background: var(--gradient-btn);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-welcome {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .sidebar-nav {
            padding: 1rem 0.75rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-section-label {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            padding: 0.5rem 0.75rem;
            margin-top: 0.5rem;
        }

        .logout-btn-container {
            padding: 1.25rem 1rem;
            border-top: 1px solid var(--border-color);
        }

        /* Sidebar Links */
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.65rem 0.9rem;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 10px;
            margin: 0.15rem 0;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateX(3px);
        }

        .sidebar-link.active {
            background: var(--gradient-btn);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
        }

        .note-link {
            font-size: 0.82rem;
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar.dark-mode .sidebar-link:hover {
            background: rgba(102, 126, 234, 0.15);
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.show { display: block; }

        /* Sidebar Toggle (Mobile) */
        .sidebar-toggle { display: none; }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem 2rem;
            min-height: 100vh;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            transition: box-shadow 0.3s ease, transform 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .card-body { padding: 2rem; }

        .card-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-color);
        }

        .dark-mode .card { color: var(--text-color); }

        /* Form Controls */
        .form-control {
            border-radius: 10px;
            border: 1.5px solid var(--border-color);
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.9);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            font-size: 0.9rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
            background: #fff;
            outline: none;
        }

        .dark-mode .form-control {
            background: rgba(45, 55, 72, 0.9);
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .dark-mode .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        /* Buttons */
        .btn {
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.6rem 1.4rem;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: var(--gradient-btn);
            border: none;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
        }

        .btn-primary:hover {
            box-shadow: 0 6px 18px rgba(102, 126, 234, 0.5);
            transform: translateY(-1px);
        }

        .btn-warning {
            background: var(--gradient-warning);
            border: none;
            color: white;
            box-shadow: 0 4px 12px rgba(240, 147, 251, 0.35);
        }

        .btn-warning:hover {
            box-shadow: 0 6px 18px rgba(240, 147, 251, 0.5);
            transform: translateY(-1px);
            color: white;
        }

        .btn-danger {
            background: var(--gradient-danger);
            border: none;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.35);
        }

        .btn-danger:hover {
            box-shadow: 0 6px 18px rgba(255, 107, 107, 0.5);
            transform: translateY(-1px);
        }

        /* Dark Mode Toggle */
        .dark-mode-toggle {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
            border-radius: 50%;
            width: 52px;
            height: 52px;
            border: none;
            background: var(--gradient-btn);
            color: white;
            font-size: 1.2rem;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4);
            transition: all 0.2s ease;
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1) rotate(15deg);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.55);
        }

        /* Summernote Dark Mode */
        body.dark-mode .note-editor {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
            border: 1px solid #4a5568 !important;
        }

        body.dark-mode .note-toolbar,
        body.dark-mode .note-statusbar {
            background-color: #1a202c !important;
        }

        body.dark-mode .note-editable {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
        }

        /* Responsive (Mobile) */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; padding: 1.5rem 1rem; }

            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
                position: fixed;
                top: 16px;
                left: 16px;
                z-index: 1001;
                background: var(--gradient-btn);
                color: white;
                border: none;
                border-radius: 12px;
                width: 44px;
                height: 44px;
                font-size: 1.1rem;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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
            <div class="sidebar-brand">
                <i class="fas fa-book-open"></i> NoteSpace
            </div>
            <div class="sidebar-welcome">
                👋 Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php" class="sidebar-link">
                <i class="fas fa-plus-circle me-2"></i>Add New Note
            </a>

            <div class="mt-2">
                <div class="sidebar-section-label">Your Notes</div>

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

            const summernoteConfig = {
                height: 250,
                toolbar: [
                    ['style', ['bold', 'underline', 'italic', 'clear']],
                    ['font', ['strikethrough']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            };

            if ($('#summernote').length) {
                $('#summernote').summernote(summernoteConfig);
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
                $('#summernote').summernote(summernoteConfig);
                $('#summernote').summernote('code', content);
            }
        }
    </script>
</body>
</html>