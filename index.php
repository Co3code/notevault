<?php
    session_start();
    include "db.php";

    // Check if user is logged in
    if (! isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
    }

    // Get all notes for sidebar
    $all_notes = mysqli_query($conn, "SELECT * FROM notes WHERE deleted_at IS NULL ORDER BY id DESC");

    // Get selected note if any
    $selected_note = null;
    if (isset($_GET['note_id'])) {
    $note_id       = (int) $_GET['note_id'];
    $result        = mysqli_query($conn, "SELECT * FROM notes WHERE id = $note_id AND deleted_at IS NULL");
    $selected_note = mysqli_fetch_assoc($result);
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Notes App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Summernote CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    <style>

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 300px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .sidebar.dark-mode {
            background: rgba(44, 62, 80, 0.95);
            color: #ecf0f1;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            flex-shrink: 0;
        }

        .sidebar.dark-mode .sidebar-header {
            border-bottom-color: rgba(255,255,255,0.1);
        }

        .sidebar-nav {
            padding: 1rem 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-link {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0 25px 25px 0;
            margin: 0.25rem 0;
            font-size: 0.9rem;
        }

        .sidebar-link:hover, .sidebar-link.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            transform: translateX(5px);
        }

        .sidebar.dark-mode .sidebar-link {
            color: #ecf0f1;
        }

        .note-link {
            padding-left: 2rem;
            font-size: 0.85rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .main-content {
            margin-left: 300px;
            padding: 2rem;
            min-height: 100vh;
        }

        h1 {
            color: white;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 2rem;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid transparent;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            background: rgba(255,255,255,0.9);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn {
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 0.5rem 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-warning {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            border: none;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            border: none;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        .dark-mode-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            border: none;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            font-size: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .logout-btn {
            position: absolute;
            bottom: 20px;
            left: 20px;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            border: none;
            border-radius: 25px;
            color: white;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ecf0f1;
        }

        .dark-mode .card {
            background: rgba(44, 62, 80, 0.95);
            color: #ecf0f1;
        }

        .dark-mode .form-control {
            background: rgba(52, 73, 94, 0.9);
            color: #ecf0f1;
            border-color: rgba(255,255,255,0.1);
        }

        .dark-mode .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .dark-mode h1 {
            color: #ecf0f1;
        }

        /* Responsive adjustments */
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
                background: linear-gradient(45deg, #667eea, #764ba2);
                color: white;
                border: none;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }

            h1 {
                font-size: 2rem;
            }

            .logout-btn {
                position: static;
                margin-top: 1rem;
                width: calc(100% - 2rem);
                margin-left: 1rem;
                margin-right: 1rem;
            }
        }

        .sidebar-toggle {
            display: none;
        }
        /* Summernote Dark Mode */
body.dark-mode .note-editor {
    background-color: #34495e !important;
    color: #ecf0f1 !important;
    border: 1px solid #2c3e50 !important;
}

body.dark-mode .note-toolbar,
body.dark-mode .note-statusbar {
    background-color: #2c3e50 !important;
    border-color: #1d3557 !important;
}

body.dark-mode .note-editable {
    background-color: #34495e !important;
    color: #ecf0f1 !important;
}

body.dark-mode .note-btn,
body.dark-mode .note-btn:hover,
body.dark-mode .note-btn:focus {
    background-color: #2c3e50 !important;
    color: #ecf0f1 !important;
    border: none !important;
}
/* Add this for text-muted in dark sidebar */
.sidebar.dark-mode .text-muted {
    color: #bdc3c7 !important;
}
    </style>

</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">
                <i class="fas fa-sticky-note me-2"></i>My Notes
            </h4>
            <small class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['user']); ?>!</small>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="sidebar-link">
                <i class="fas fa-plus-circle me-2"></i>Add New Note
            </a>
            <div class="mt-3">
                <small class="text-muted px-3">Your Notes:</small>
                <?php while ($note = mysqli_fetch_assoc($all_notes)): ?>
                    <a href="index.php?note_id=<?php echo $note['id']; ?>" class="sidebar-link note-link <?php echo($selected_note && $selected_note['id'] == $note['id']) ? 'active' : ''; ?>">
                        <i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars(substr($note['title'], 0, 25)); ?><?php echo strlen($note['title']) > 25 ? '...' : ''; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </nav>
        <a href="logout.php" class="btn logout-btn">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button class="btn sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="text-center mb-4">
            <i class="fas fa-pencil me-2"></i>Drafts

        </h1>

        <?php if ($selected_note): ?>
            <!-- View/Edit Selected Note -->
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-file-alt me-2"></i><?php echo htmlspecialchars($selected_note['title']); ?>
                    </h5>
                    <!--
                    <p class="card-text"> echo nl2br(htmlspecialchars($selected_note['content'])); ?></p>-->
                    <p class="card-text"><?php echo $selected_note['content']; ?></p>

                    <div class="d-flex justify-content-between mt-3">
                        <a href="edit_note.php?id=<?php echo $selected_note['id']; ?>" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="delete_note.php?id=<?php echo $selected_note['id']; ?>"
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
                    <form action="save_note.php" method="POST">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
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