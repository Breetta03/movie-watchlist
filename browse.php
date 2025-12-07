<?php
include 'config.php';


session_start();
$filter_genre = isset($_GET['genre']) ? $_GET['genre'] : 'all';

// -----------------------------------------
// Handle Add to Watchlist
// -----------------------------------------
$message = "";
if (isset($_POST['add_id'])) {
    $movieId = (int)$_POST['add_id'];

    // Check if already in watchlist
    $check = mysqli_query($conn, "SELECT id FROM movies WHERE title = (
        SELECT title FROM movies_master WHERE id = $movieId
    )");

    if (mysqli_num_rows($check) > 0) {
        $message = "⚠ Already in your watchlist!";
    } else {
        // Insert into watchlist
        $sqlCopy = "
            INSERT INTO movies (title, genre, year, watched, rating, review)
            SELECT title, genre, year, 0, 0, ''
            FROM movies_master
            WHERE id = $movieId
        ";
        mysqli_query($conn, $sqlCopy);
        $message = "✅ Movie added to your watchlist!";
    }
}

// -----------------------------------------
// Filters
// -----------------------------------------
$search = "";
$searchSql = "";

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $searchSql = "WHERE title LIKE '%$search%'";
}

// Genre Filter
$genre = "";
$genreSql = "";

if (!empty($_GET['genre']) && $_GET['genre'] != "all") {
    $genre = mysqli_real_escape_string($conn, $_GET['genre']);
    if ($searchSql == "") {
        $genreSql = "WHERE genre='$genre'";
    } else {
        $genreSql = " AND genre='$genre'";
    }
}

// Final Query
$sql = "SELECT * FROM movies_master $searchSql $genreSql ORDER BY year DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Browse Movies</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        /* Card hover animation */
        .movie-card:hover {
            transform: scale(1.03);
            box-shadow: 0px 4px 15px rgba(0,0,0,0.15);
            transition: 0.25s ease;
        }

        .added {
            background: #d4ffd4 !important;
            border-left: 6px solid #2ecc71;
            animation: blinkAdd 0.4s ease;
        }

        @keyframes blinkAdd {
            from { background:#b6ffb6; }
            to { background:#d4ffd4; }
        }

        /* Dropdown styling */
        .genre-box select {
            padding: 8px;
            border-radius: 8px;
            border: 2px solid #ccc;
            outline: none;
        }
    </style>
</head>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.querySelector("input[name='search']");
    const cards = document.querySelectorAll(".movie-card");

    searchInput.addEventListener("keyup", function () {
        const value = searchInput.value.toLowerCase().trim();

        cards.forEach(card => {
            const title = card.querySelector(".movie-title").innerText.toLowerCase();

            if (title.includes(value)) {
                card.style.display = "block"; // show
            } else {
                card.style.display = "none"; // hide
            }
        });
    });
});
</script>

<body>
<div class="page-shell">

    <div class="top-bar">
        <div>
            <div class="app-title">🎬 Browse Movies</div>
            <div class="app-subtitle">Search from the master list and add movies to your personal watchlist.</div>
        </div>
        <div class="top-right-buttons">
            <a href="list.php" class="pill-btn pill-primary">My Watchlist</a>
            <a href="index.php" class="pill-btn pill-outline">Home</a>
        </div>
    </div>

    <?php if (!empty($message)) { ?>
        <div class="toast-success"><?= htmlspecialchars($message) ?></div>
    <?php } ?>

    <div class="filter-row">
        <form class="search-box" method="GET" action="browse.php">
            <input type="text" name="search" placeholder="Search movies..." value="<?= htmlspecialchars($search) ?>">
        </form>

        <form method="GET" action="browse.php">
            <?php if (!empty($search)) { ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
            <?php } ?>
            <select name="genre" class="genre-select" onchange="this.form.submit()">
                <option value="all" <?= ($filter_genre=='all' || $filter_genre=='')?'selected':'' ?>>All Genres</option>
                <option value="Action" <?= $filter_genre=='Action'?'selected':'' ?>>Action</option>
                <option value="Comedy" <?= $filter_genre=='Comedy'?'selected':'' ?>>Comedy</option>
                <option value="Romance" <?= $filter_genre=='Romance'?'selected':'' ?>>Romance</option>
                <option value="Sci-Fi" <?= $filter_genre=='Sci-Fi'?'selected':'' ?>>Sci-Fi</option>
                <option value="Epic" <?= $filter_genre=='Epic'?'selected':'' ?>>Epic</option>
                <option value="Sports/Action" <?= $filter_genre=='Sports/Action'?'selected':'' ?>>Sports/Action</option>
            </select>
        </form>
    </div>

    <div class="movie-grid">
        <div class="movie-grid">

    <!-- No Results Box -->
    <div id="no-results" style="
        display:none;
        text-align:center;
        width:100%;
        padding:40px 0;
        color:#fff;
        font-size:22px;
        opacity:0.8;
    ">
        ❌ No results found
    </div>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="movie-card">
                <div class="movie-title"><?= htmlspecialchars($row['title']); ?></div>
                <div class="movie-meta">
                    <?= htmlspecialchars($row['genre']); ?>
                    <?php if (!empty($row['year'])) echo " • " . (int)$row['year']; ?>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_id" value="<?= $row['id']; ?>">
                    <button class="pill-btn pill-primary" type="submit">+ Add to Watchlist</button>
                </form>
            </div>
        <?php } ?>
    </div>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.querySelector("input[name='search']");
    const cards = document.querySelectorAll(".movie-card");
    const noResults = document.getElementById("no-results");

    function filterMovies() {
        const value = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        cards.forEach(card => {
            const title = card.querySelector(".movie-title").innerText.toLowerCase();

            if (title.includes(value)) {
                card.style.display = "block";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        // Show or hide "No Results Found"
        noResults.style.display = visibleCount === 0 ? "block" : "none";
    }

    searchInput.addEventListener("keyup", filterMovies);
});
</script>

</body>

</html>
