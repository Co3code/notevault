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

    $user_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
    header("Location: admin_dashboard.php");
    exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    if (isset($_POST['update'])) {
    $username = $_POST['username'];
    $role     = $_POST['role'];

    if (! empty($_POST['password'])) {
        $password    = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET username = ?, role = ?, password = ? WHERE id = ?");
        $update_stmt->bind_param("sssi", $username, $role, $password, $user_id);
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $username, $role, $user_id);
    }

    $update_stmt->execute();
    $update_stmt->close();

    header("Location: admin_dashboard.php?message=User updated successfully");
    exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Admin</title>
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
        }

        .edit-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            border: 1px solid #e2e8f0;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #fcfdfe;
        }

        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-update {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(99, 102, 241, 0.4);
        }

        .cancel-link {
            text-decoration: none;
            color: #94a3b8;
            font-size: 0.9rem;
            font-weight: 500;
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            transition: color 0.2s;
        }

        .cancel-link:hover { color: #64748b; }

        .id-badge {
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 6px;
            float: right;
        }
    </style>
</head>
<body>

<div class="edit-card">
    <span class="id-badge">User ID: #<?php echo $user_id; ?></span>
    <div class="header-icon">
        <i class="fa-solid fa-user-gear"></i>
    </div>

    <h3 class="fw-bold mb-1">Edit Account</h3>
    <p class="text-muted mb-4">Modify permissions and user credentials.</p>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-regular fa-user"></i></span>
                <input type="text" name="username" class="form-control border-start-0" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Update Password</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••">
            </div>
            <div class="form-text text-xs">Leave blank to keep current password.</div>
        </div>

        <div class="mb-4">
            <label class="form-label">Access Role</label>
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fa-solid fa-shield-halved"></i></span>
                <select name="role" class="form-select border-start-0">
                    <option value="user" <?php echo($user['role'] == 'user') ? 'selected' : ''; ?>>Standard User</option>
                    <option value="admin" <?php echo($user['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                </select>
            </div>
        </div>

        <button type="submit" name="update" class="btn btn-primary btn-update w-100 mb-2">
            Save Changes
        </button>

        <a href="admin_dashboard.php" class="cancel-link">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </form>
</div>

</body>
</html>