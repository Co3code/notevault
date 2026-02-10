<?php include "db.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Notes App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>

  /* body, .card, input, textarea {
    transition: background-color 0.3s, color 0.3s, border-color 0.3s;
  }

  body.dark-mode {
    background-color: #121212 !important;
    color: #f1f1f1 !important;
  }

  .card.dark-mode {
    background-color: #1e1e1e !important;
    color: #f1f1f1 !important;
  }


  input.dark-mode, textarea.dark-mode {
    background-color: #1e1e1e !important;
    color: #f1f1f1 !important;
    border-color: #444 !important;
  }


  .btn-darkmode {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
  } */
</style>
</head>

<body class="bg-light">
<div class="container py-5">

<h1 class="text-center mb-4"> My Notes</h1>

<!-- Add Note -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="save_note.php" method="POST">
            <input type="text" name="title" class="form-control mb-3" placeholder="Note title" required>
            <textarea name="content" class="form-control mb-3" rows="4" placeholder="Write your note..." required></textarea>
            <button class="btn btn-primary w-100">Save Note</button>
        </form>
    </div>
</div>

<!-- Notes -->
<div class="row">
<?php
    /// Old query (fetched all notes, including deleted ones)
    // $notes = mysqli_query($conn, "SELECT * FROM notes ORDER BY id DESC");

    // New query: only show notes that are not soft-deleted
    $notes = mysqli_query($conn, "SELECT * FROM notes WHERE deleted_at IS NULL ORDER BY id DESC");

    if (mysqli_num_rows($notes) == 0) {
    echo "<p class='text-center text-muted'>No notes yet.</p>";
    }

    while ($note = mysqli_fetch_assoc($notes)) {
    ?>
    <div class="col-md-4 mb-3">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5><?php echo htmlspecialchars($note['title']) ?></h5>
                <p><?php echo nl2br(htmlspecialchars($note['content'])) ?></p>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="edit_note.php?id=<?php echo $note['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                <a href="delete_note.php?id=<?php echo $note['id'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Are you sure?')">
                   Delete
                </a>
            </div>
        </div>
    </div>
<?php }?>
</div>

</div>

<!-- Dark Mode Toggle Button
<button class="btn btn-secondary btn-darkmode" onclick="toggleDarkMode()">🌙 Dark Mode</button>-->

</body>
</html>

<!-- <script>
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');

    // Make all cards dark as well
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => card.classList.toggle('dark-mode'));
}
</script>
-->

<!-- <script> //emember the dark mode even after refresh:
document.addEventListener('DOMContentLoaded', () => {
    if(localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
        document.querySelectorAll('.card').forEach(card => card.classList.add('dark-mode'));
    }
});

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    document.querySelectorAll('.card').forEach(card => card.classList.toggle('dark-mode'));

    if(document.body.classList.contains('dark-mode')){
        localStorage.setItem('darkMode', 'enabled');
    } else {
        localStorage.setItem('darkMode', 'disabled');
    }
}
</script> -->

<!-- <script>
/* Dark Mode with Input/Textarea and Cards, and remember preference */
document.addEventListener('DOMContentLoaded', () => {
    const isDark = localStorage.getItem('darkMode') === 'enabled';
    if(isDark){
        document.body.classList.add('dark-mode');
        document.querySelectorAll('.card, input, textarea').forEach(el => el.classList.add('dark-mode'));
    }
});

function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');

    // Toggle dark mode on all cards, inputs, and textarea
    document.querySelectorAll('.card, input, textarea').forEach(el => el.classList.toggle('dark-mode'));

    // Save preference
    if(document.body.classList.contains('dark-mode')){
        localStorage.setItem('darkMode', 'enabled');
    } else {
        localStorage.setItem('darkMode', 'disabled');
    }
}
</script> -->
