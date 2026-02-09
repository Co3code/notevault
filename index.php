<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Notes App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-5">

    <h1 class="text-center mb-4">📝 My Notes</h1>

    <!-- Add Note -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="save_note.php">
                <input type="text" name="title" class="form-control mb-3" placeholder="Note title" required>
                <textarea name="content" class="form-control mb-3" rows="4" placeholder="Write your note here..." required></textarea>
                <button class="btn btn-primary w-100">Save Note</button>
            </form>
        </div>
    </div>

    <!-- Notes -->
    <div class="row">
        <?php
            $notes = mysqli_query($conn, "SELECT * FROM notes ORDER BY id DESC");

            if (mysqli_num_rows($notes) == 0) {
                echo "<p class='text-center text-muted'>No notes yet.</p>";
            }

            while ($note = mysqli_fetch_assoc($notes)) {
            ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo htmlspecialchars($note['title']) ?>
                        </h5>
                        <p class="card-text">
                            <?php echo nl2br(htmlspecialchars($note['content'])) ?>
                        </p>
                    </div>
                    <div class="card-footer text-muted small">
                        <?php echo $note['created_at'] ?>
                    </div>
                </div>
            </div>
        <?php }?>
    </div>

</div>

</body>
</html>
