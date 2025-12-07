<?php
include 'config.php';
session_start();

$id = $_GET['id'];

// Fetch movie
$sql = "SELECT * FROM movies WHERE id=$id";
$res = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($res);

// Update movie
if (isset($_POST['update'])) {
    $watched = isset($_POST['watched']) ? 1 : 0;
    $rating = (int)$_POST['rating'];
    $review = mysqli_real_escape_string($conn, $_POST['review']);

    $update = "
        UPDATE movies 
        SET watched=$watched, rating=$rating, review='$review'
        WHERE id=$id
    ";
    mysqli_query($conn, $update);

    header("Location: list.php?updated=1&highlight=$id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Update Movie</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        .update-box {
            max-width: 750px;
            margin: 50px auto;
            padding: 25px;
            border-radius: 18px;
            background: radial-gradient(circle at top, #1e1b4b 0, #020617 55%);
            border: 1px solid #a855f766;
            box-shadow: 0 15px 40px rgba(0,0,0,0.7);
        }

        .update-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .update-meta {
            font-size: 14px;
            color: #cbd5e1;
            margin-bottom: 16px;
        }

        .update-form input, 
        .update-form textarea, 
        .update-form select {
            width: 100%;
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 10px;
            background: rgba(15,23,42,0.9);
            border: none;
            color: white;
            outline: none;
        }

        .update-form label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 4px;
            display: block;
        }
    </style>
</head>
<body>

<div class="container">
    <a class="btn btn-outlined" href="list.php">← Back</a>
</div>

<div class="update-box">

    <div class="update-title"><?= $row['title'] ?></div>
    <div class="update-meta"><?= $row['genre'] ?> • <?= $row['year'] ?></div>

    <form class="update-form" method="POST">

        <label>Watched Status</label>
        <input type="checkbox" name="watched" <?= $row['watched'] ? 'checked' : '' ?>> Mark as Watched

        <label>Rating (1-5)</label>
        <select name="rating">
            <option value="0">No rating</option>
            <?php for ($i=1; $i<=5; $i++) { ?>
                <option value="<?= $i ?>" <?= $row['rating']==$i?'selected':'' ?>>
                    <?= $i ?>/5
                </option>
            <?php } ?>
        </select>

        <label>Review</label>
        <textarea name="review" rows="4"><?= htmlspecialchars($row['review']) ?></textarea>

        <button name="update" class="btn btn-primary">Save Changes</button>
    </form>

</div>

</body>
</html>
