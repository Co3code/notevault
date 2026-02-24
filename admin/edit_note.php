<?php
    session_start();
    include '../config/db.php';

    if (! isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
    }

    if (! isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
    }

    $note_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM notes WHERE id = ? AND deleted_at IS NULL");
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
    header("Location: admin_dashboard.php");
    exit();
    }

    $note = $result->fetch_assoc();
    $stmt->close();

    if (isset($_POST['update'])) {
    $title   = $_POST['title'];
    $content = $_POST['content'];

    $update_stmt = $conn->prepare("UPDATE notes SET title = ?, content = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $title, $content, $note_id);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: admin_dashboard.php?message=Note updated successfully");
    exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note | Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .edit-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 600px; /* Wider for note content */
            padding: 2.5rem;
            border: 1px solid #e2e8f0;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); /* Green gradient for notes */
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #fcfdfe;
            resize: none;
        }

        .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .btn-update {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .cancel-link {
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
            display: block;
            text-align: center;
            margin-top: 1.5rem;
        }

        .cancel-link:hover { color: #64748b; }

        .note-id {
            background: #ecfdf5;
            color: #059669;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="edit-card">
    <div class="d-flex justify-content-between align-items-start">
        <div class="header-icon">
            <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <span class="note-id">Note #<?php echo $note_id; ?></span>
    </div>

    <h3 class="fw-bold mb-1">Edit Note</h3>
    <p class="text-muted mb-4">Update the title or refine the content of this entry.</p>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Note Title</label>
            <input type="text" name="title" class="form-control fw-bold"
                   value="<?php echo htmlspecialchars($note['title']); ?>"
                   placeholder="Enter a catchy title..." required>
        </div>

        <div class="mb-4">
            <label class="form-label">Note Content</label>
            <textarea name="content" class="form-control" rows="8"
                      placeholder="Write your thoughts here..." required><?php echo htmlspecialchars($note['content']); ?></textarea>
        </div>

        <button type="submit" name="update" class="btn btn-update w-100 mb-2">
            <i class="fa-solid fa-floppy-disk me-2"></i> Save Note
        </button>

        <a href="admin_dashboard.php" class="cancel-link">
            <i class="fa-solid fa-chevron-left me-1"></i> Discard changes
        </a>
    </form>
</div>

</body>
</html>