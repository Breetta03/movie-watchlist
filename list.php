<?php
include 'config.php';
session_start();

// -----------------------------------
// FILTERS
// -----------------------------------
$filter = "";
$activeTab = "all";

if (isset($_GET['type'])) {
    if ($_GET['type'] == "watched") {
        $filter = "WHERE watched = 1";
        $activeTab = "watched";
    } elseif ($_GET['type'] == "unwatched") {
        $filter = "WHERE watched = 0";
        $activeTab = "unwatched";
    }
}

// -----------------------------------
// SEARCH
// -----------------------------------
$search = "";
$searchSql = "";

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    if ($filter == "") {
        $searchSql = "WHERE title LIKE '%$search%'";
    } else {
        $searchSql = " AND title LIKE '%$search%'";
    }
}

// -----------------------------------
// STATS
// -----------------------------------
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM movies"))['c'];
$watched = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM movies WHERE watched=1"))['c'];
$pending = $total - $watched;

$avgRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT AVG(rating) AS a FROM movies WHERE rating>0"));
$avgRating = round($avgRow['a'] ?? 0, 2);

// -----------------------------------
// FINAL QUERY
// -----------------------------------
$sql = "SELECT * FROM movies $filter $searchSql ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

$highlightId = $_GET['highlight'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Watchlist</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        /* Split layout card */
        .movie-row {
            display: flex;
            justify-content: space-between;
            background: radial-gradient(circle at top, #1e1b4b 0, #020617 55%);
            border: 1px solid rgba(148,163,184,0.3);
            padding: 18px;
            margin-bottom: 14px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.6);
            transition: 0.25s ease;
            cursor: pointer;
        }

        .movie-row:hover {
            transform: translateY(-3px);
            border-color: #a855f7aa;
        }

        .left-info {
            width: 70%;
        }

        .right-actions {
            width: 28%;
            text-align: right;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .title-big {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .meta-line {
            font-size: 14px;
            color: #cbd5e1;
            margin-bottom: 6px;
        }

        .rating-line {
            font-size: 14px;
            margin-bottom: 6px;
        }

        .review-box {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 8px;
        }

        .row-badge {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .watch-btn {
            margin-bottom: 6px;
        }

        .delete-btn {
            margin-top: 6px;
            color: #f87171;
            text-decoration: none;
            font-size: 13px;
        }

        .card-highlight {
            animation: glowRow 0.8s ease-out;
            border-color: #22c55e;
        }

        @keyframes glowRow {
            0% { opacity: 0.6; transform: scale(0.98); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
<div class="page-shell">

    <div class="top-bar">
        <div>
            <div class="app-title">🎬 My Watchlist</div>
            <div class="app-subtitle">All movies you’ve saved from Browse and updated with ratings.</div>
        </div>
        <div class="top-right-buttons">
            <a href="browse.php" class="pill-btn pill-primary">Browse Movies</a>
            <a href="index.php" class="pill-btn pill-outline">Home</a>
        </div>
    </div>

    <?php if (!empty($_GET['updated'])) { ?>
        <div class="toast-success">✔ Movie updated successfully!</div>
    <?php } ?>

    <div class="stats-row">
        <span class="stat-chip">Total: <?= $total ?></span>
        <span class="stat-chip">Watched: <?= $watched ?></span>
        <span class="stat-chip">Pending: <?= $pending ?></span>
        <span class="stat-chip">Avg Rating: <?= $avgRating ?>/5</span>
    </div>

    <div class="top-bar">
        <div class="tabs">
            <a href="list.php" class="tab <?= $activeTab=='all'?'tab-active':'' ?>">All</a>
            <a href="list.php?type=watched" class="tab <?= $activeTab=='watched'?'tab-active':'' ?>">Watched</a>
            <a href="list.php?type=unwatched" class="tab <?= $activeTab=='unwatched'?'tab-active':'' ?>">Unwatched</a>
        </div>

        <form class="search-box" method="GET" action="list.php">
            <?php if ($activeTab != 'all') { ?>
                <input type="hidden" name="type" value="<?= $activeTab ?>">
            <?php } ?>
            <input type="text" name="search" placeholder="Search in watchlist..." value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="movie-row <?= $row['id']==$highlightId?'row-highlight':'' ?>"
             onclick="window.location='watch.php?id=<?= $row['id'] ?>'">

            <div class="left-info">
                <div class="title-big"><?= htmlspecialchars($row['title']) ?></div>
                <div class="meta-line">
                    <?= htmlspecialchars($row['genre']) ?>
                    <?php if (!empty($row['year'])) echo " • " . (int)$row['year']; ?>
                </div>
                <div class="rating-line">⭐ <?= (int)$row['rating'] ?>/5</div>
                <?php if (!empty($row['review'])) { ?>
                    <div class="review-text">“<?= nl2br(htmlspecialchars($row['review'])) ?>”</div>
                <?php } ?>
            </div>

            <div class="right-actions">
                <span class="status-pill <?= $row['watched']?'status-watched':'status-unwatched' ?>">
                    <?= $row['watched'] ? 'Watched' : 'Not Watched' ?>
                </span>

                <div class="right-buttons">
                    <a href="watch.php?id=<?= $row['id'] ?>" class="pill-btn pill-primary small-btn"
                       onclick="event.stopPropagation()">Update</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="delete-link"
                       onclick="event.stopPropagation(); return confirm('Delete this movie?');">
                        Delete
                    </a>
                </div>
            </div>
        </div>
    <?php } ?>

</div>
</body>

</html>
