<?php
include 'config.php';
session_start();

if (isset($_POST['add'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $year = (int)$_POST['year'];

    $sql = "INSERT INTO movies_master (title, genre, year)
            VALUES ('$title', '$genre', $year)";
    mysqli_query($conn, $sql);

    header("Location: browse.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Movie</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        .add-box {
            max-width: 450px;
            margin: 70px auto;
            padding: 24px;
            border-radius: 18px;
            background: radial-gradient(circle at top, #1e1b4b 0, #020617 60%);
            border: 1px solid #a855f766;
            box-shadow: 0 15px 40px rgba(0,0,0,0.7);
        }
        .add-box h2 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="page-shell">

    <div class="form-card">
        <div class="form-header">
            <h2>Add Movie to Master List</h2>
            <p>Add base movies to your main library. Then you can add them to your watchlist from Browse.</p>
        </div>

        <?php if (!empty($message)) { ?>
            <div class="toast-success"><?= htmlspecialchars($message) ?></div>
        <?php } ?>

        <form method="POST">
            <div class="field">
                <label>Movie Title</label>
                <input type="text" name="title" required>
            </div>

            <div class="field">
                <label>Genre</label>
                <input type="text" name="genre" required>
            </div>

            <div class="field">
                <label>Year</label>
                <input type="number" name="year" placeholder="2024">
            </div>

            <button type="submit" name="add" class="pill-btn pill-primary">Add Movie</button>
        </form>

        <div class="form-footer">
            <a href="index.php" class="pill-btn pill-outline pill-small">Back to Home</a>
        </div>
    </div>

</div>
</body>

</html>
