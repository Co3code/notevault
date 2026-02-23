<?php
    session_start();
    include "db.php";

    // Ensure user is logged in
    if (! isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
    }
    $user_id = $_SESSION['user_id'];

    // Validate note ID
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    if ($id <= 0) {
    header("Location: index.php");
    exit();
    }

    // Fetch note only if it belongs to this user and not deleted
    $stmt = $conn->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ? AND deleted_at IS NULL");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $note   = $result->fetch_assoc();

    if (! $note) {
    // Note not found or not allowed
    header("Location: index.php");
    exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note - My Notes App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<!-- adding link summernote-->
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
            width: 250px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.dark-mode {
            background: rgba(44, 62, 80, 0.95);
            color: #ecf0f1;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .sidebar-header.dark-mode {
            border-bottom-color: rgba(255,255,255,0.1);
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-link {
            display: block;
            padding: 0.75rem 1.5rem;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 0 25px 25px 0;
            margin: 0.25rem 0;
        }

        .sidebar-link:hover {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-link.dark-mode {
            color: #ecf0f1;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
        }

        h1, h2 {
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

        .btn-secondary {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
            border: none;
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

        .dark-mode h1, .dark-mode h2 {
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

            h1, h2 {
                font-size: 2rem;
            }
        }

        .sidebar-toggle {
            display: none;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4 class="mb-0">
                My Notes
            </h4>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="sidebar-link">
                <i class="fas fa-plus-circle me-2"></i>Add Note
            </a>
            <a href="index.php" class="sidebar-link">
                <i class="fas fa-list me-2"></i>View Notes
            </a>
        </nav>
    </div>

    <!-- Mobile Sidebar Toggle -->
    <button class="btn sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="text-center mb-4">
            <i class="fas fa-edit me-2"></i>Edit Note
        </h2>

        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow">
                    <div class="card-body">
                        <form action="update_note.php" method="POST">
                            <input type="hidden" name="id" value="<?php echo $note['id'] ?>">
                            <div class="mb-3">
                                <label for="title" class="form-label">
                                    <i class="fas fa-heading me-2"></i>Note Title
                                </label>
                                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($note['title']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="content" class="form-label">
                                    <i class="fas fa-file-alt me-2"></i>Note Content
                                </label>
                                <!--
                                    <textarea name="content" id="content" class="form-control" rows="8" required><?php echo htmlspecialchars($note['content']) ?></textarea>
                                -->
                                <textarea name="content" id="summernote" class="form-control" rows="8" placeholder="Write your note..." required></textarea>

                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Notes
                                </a>
                                <button class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Note
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dark Mode Toggle Button -->
    <button class="btn dark-mode-toggle" onclick="toggleDarkMode()" id="darkModeBtn">
        <i class="fas fa-moon"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Dark Mode Toggle with Persistence
        document.addEventListener('DOMContentLoaded', () => {
            const isDark = localStorage.getItem('darkMode') === 'enabled';
            if (isDark) {
                document.body.classList.add('dark-mode');
                document.getElementById('sidebar').classList.add('dark-mode');
                document.querySelectorAll('.sidebar-link').forEach(link => link.classList.add('dark-mode'));
                document.getElementById('darkModeBtn').innerHTML = '<i class="fas fa-sun"></i>';
            }
        });

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            document.getElementById('sidebar').classList.toggle('dark-mode');
            document.querySelectorAll('.sidebar-link').forEach(link => link.classList.toggle('dark-mode'));

            const isDark = document.body.classList.contains('dark-mode');

            // Update button icon
            const btn = document.getElementById('darkModeBtn');
            btn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';

            // Save preference
            localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
        }
    </script>
    <!--  fixing the old content to show when editing-->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>
    <script>
document.addEventListener("DOMContentLoaded", function() {

    // Initialize Summernote
    $('#summernote').summernote({
        height: 250
    });

    // Load old content into editor
    $('#summernote').summernote('code', <?php echo json_encode($note['content']); ?>);

});
</script>


</body>
</html>