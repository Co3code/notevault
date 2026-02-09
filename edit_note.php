<?php
    include "db.php";

    $id     = $_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM notes WHERE id=$id");
    $note   = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-5">

<h2 class="mb-4">✏️ Edit Note</h2>

<form action="update_note.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $note['id'] ?>">
    <input type="text" name="title" class="form-control mb-3" value="<?php echo htmlspecialchars($note['title']) ?>" required>
    <textarea name="content" class="form-control mb-3" rows="5" required><?php echo htmlspecialchars($note['content']) ?></textarea>
    <button class="btn btn-success w-100">Update Note</button>
</form>

</div>
</body>
</html>
