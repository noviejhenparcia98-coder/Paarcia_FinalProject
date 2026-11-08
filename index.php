<?php
require 'db_connect.php';

// Search functionality
$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare("
        SELECT * FROM books
        WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?
        ORDER BY id DESC
    ");

    $term = "%$search%";
    $stmt->execute([$term, $term, $term]);
} else {
    $stmt = $pdo->query("SELECT * FROM books ORDER BY id DESC");
}

$books = $stmt->fetchAll();

// Dashboard statistics
$totalBooks = $pdo->query(
    "SELECT COUNT(*) FROM books"
)->fetchColumn();

$totalCopies = $pdo->query(
    "SELECT COALESCE(SUM(quantity), 0) FROM books"
)->fetchColumn();

$totalAuthors = $pdo->query(
    "SELECT COUNT(DISTINCT author) FROM books"
)->fetchColumn();

// Get author information
$authors = $pdo->query("
    SELECT author, COUNT(*) AS book_count
    FROM books
    GROUP BY author
    ORDER BY author
")->fetchAll();

// Escape output
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Library Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="style.css" rel="stylesheet">
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            📚 Library Management
        </a>
    </div>
</nav>

<div class="container py-4">

    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h2 class="fw-bold">Library Dashboard</h2>
            <p class="text-muted">Manage your library collection</p>
        </div>

        <a href="add_book.php" class="btn btn-primary">
            + Add New Book
        </a>
    </div>

    <!-- Success Message -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= e($_GET['msg']) ?>

            <button class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Dashboard Cards -->
    <div class="row g-3 mb-4">

        <!-- Total Book Titles -->
        <div class="col-md-4">
            <div class="card stat-card"
                 onclick="showTable('titlesTable')"
                 role="button"
                 tabindex="0"
                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();showTable('titlesTable');}">

                <div class="card-body">
                    <h6>📚 Total Book Titles</h6>
                    <h2><?= e($totalBooks) ?></h2>
                    <small>Click to view book titles</small>
                </div>
            </div>
        </div>

        <!-- Total Book Copies -->
        <div class="col-md-4">
            <div class="card stat-card"
                 onclick="showTable('copiesTable')"
                 role="button"
                 tabindex="0"
                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();showTable('copiesTable');}">

                <div class="card-body">
                    <h6>📖 Total Book Copies</h6>
                    <h2><?= e($totalCopies) ?></h2>
                    <small>Click to view book copies</small>
                </div>
            </div>
        </div>

        <!-- Total Authors -->
        <div class="col-md-4">
            <div class="card stat-card"
                 onclick="showTable('authorsTable')"
                 role="button"
                 tabindex="0"
                 onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();showTable('authorsTable');}">

                <div class="card-body">
                    <h6>✍️ Total Authors</h6>
                    <h2><?= e($totalAuthors) ?></h2>
                    <small>Click to view authors</small>
                </div>
            </div>
        </div>

    </div>

    <!-- Search -->
    <div class="card mb-4">
        <div class="card-body">

            <form method="GET" action="index.php" class="row g-2">

                <div class="col-md-10">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search by title, author, or ISBN"
                        value="<?= e($search) ?>">
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary">
                        Search
                    </button>
                </div>

            </form>

            <?php if ($search !== ''): ?>
                <div class="mt-2">
                    <small class="text-muted">
                        Search results for:
                        <strong><?= e($search) ?></strong>
                    </small>

                    <a href="index.php" class="btn btn-sm btn-outline-secondary ms-2">
                        Clear Search
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Tables Container: Hidden by default -->
    <div id="dashboardTables" style="display: none;">

        <!-- BOOK TITLES TABLE -->
        <div id="titlesTable" class="dashboard-table card mb-4">

            <div class="card-header">
                <h5 class="mb-0">📚 Book Titles</h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ISBN</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Genre</th>
                                <th>Year</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($books) > 0): ?>

                            <?php foreach ($books as $book): ?>

                                <tr>
                                    <td><?= e($book['id']) ?></td>
                                    <td><?= e($book['isbn']) ?></td>
                                    <td><?= e($book['title']) ?></td>
                                    <td><?= e($book['author']) ?></td>
                                    <td><?= e($book['genre']) ?></td>
                                    <td><?= e($book['publication_year']) ?></td>

                                    <td>
                                        <span class="badge bg-primary">
                                            <?= e($book['quantity']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <a href="edit_book.php?id=<?= e($book['id']) ?>"
                                           class="btn btn-sm btn-warning">
                                            Edit
                                        </a>

                                        <form action="delete_book.php"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this book?')">

                                            <input type="hidden"
                                                   name="id"
                                                   value="<?= e($book['id']) ?>">

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="text-center">
                                    No books found.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>
                    </table>

                </div>
            </div>
        </div>

        <!-- BOOK COPIES TABLE -->
        <div id="copiesTable" class="dashboard-table card mb-4">

            <div class="card-header">
                <h5 class="mb-0">📖 Book Copies</h5>
            </div>

            <div class="card-body">

                <p>
                    Total book copies:
                    <strong><?= e($totalCopies) ?></strong>
                </p>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($books as $book): ?>

                            <tr>
                                <td><?= e($book['title']) ?></td>
                                <td><?= e($book['author']) ?></td>
                                <td><?= e($book['isbn']) ?></td>
                                <td><?= e($book['quantity']) ?></td>
                            </tr>

                        <?php endforeach; ?>

                        </tbody>
                    </table>

                </div>
            </div>
        </div>

        <!-- AUTHORS TABLE -->
        <div id="authorsTable" class="dashboard-table card mb-4">

            <div class="card-header">
                <h5 class="mb-0">✍️ Authors</h5>
            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Author Name</th>
                                <th>Number of Book Titles</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($authors) > 0): ?>

                            <?php foreach ($authors as $author): ?>

                                <tr>
                                    <td><?= e($author['author']) ?></td>
                                    <td><?= e($author['book_count']) ?></td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="2" class="text-center">
                                    No authors found.
                                </td>
                            </tr>

                        <?php endif; ?>

                        </tbody>
                    </table>

                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="text-center text-muted mt-4">
        FB: NOVIE PARCIA | GMAIL: noviejhenparcia98@gmail.com
    </footer>

</div>

<!-- Dashboard Card Function -->
<script>
function showTable(tableId) {

    const container = document.getElementById('dashboardTables');
    const tables = document.querySelectorAll('.dashboard-table');

    // Show the tables container
    container.style.display = 'block';

    // Hide all tables first
    tables.forEach(function(table) {
        table.style.display = 'none';
    });

    // Show the selected table
    const selectedTable = document.getElementById(tableId);

    if (selectedTable) {
        selectedTable.style.display = 'block';

        selectedTable.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Automatically show Book Titles table after searching
window.addEventListener('DOMContentLoaded', function() {

    const searchParams = new URLSearchParams(window.location.search);

    if (searchParams.has('search')) {
        showTable('titlesTable');
    }

});
</script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>