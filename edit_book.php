<?php
require 'db_connect.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Get book ID from URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Also allow ID from POST when submitting the form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

if (!$id || $id <= 0) {
    header("Location: index.php?msg=" . urlencode("Invalid book ID."));
    exit;
}

// Fetch book information
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    header("Location: index.php?msg=" . urlencode("Book not found."));
    exit;
}

$error = "";

// Update book
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isbn = trim($_POST['isbn'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $publication_year = trim($_POST['publication_year'] ?? '');
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if (
        $isbn === '' ||
        $title === '' ||
        $author === '' ||
        $genre === '' ||
        $quantity === false ||
        $quantity === null ||
        $quantity < 0
    ) {
        $error = "Please fill in all required fields correctly.";
    } else {

        try {
            $stmt = $pdo->prepare("
                UPDATE books
                SET isbn = ?,
                    title = ?,
                    author = ?,
                    genre = ?,
                    publication_year = ?,
                    quantity = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $isbn,
                $title,
                $author,
                $genre,
                $publication_year !== '' ? $publication_year : null,
                $quantity,
                $id
            ]);

            header("Location: index.php?msg=" . urlencode("Book updated successfully!"));
            exit;

        } catch (PDOException $e) {
            error_log("Edit error: " . $e->getMessage());

            if ($e->getCode() == '23000') {
                $error = "This ISBN already exists. Please use a different ISBN.";
            } else {
                $error = "Unable to update the book. Please check your database.";
            }
        }
    }

    // Preserve submitted values when validation fails
    $book['isbn'] = $isbn;
    $book['title'] = $title;
    $book['author'] = $author;
    $book['genre'] = $genre;
    $book['publication_year'] = $publication_year;
    $book['quantity'] = $_POST['quantity'] ?? $book['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Edit Book - Library Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link href="style.css" rel="stylesheet">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            📚 Library Management
        </a>
    </div>
</nav>

<div class="container py-4">

    <div class="card shadow">

        <div class="card-header">
            <h4 class="mb-0">✏️ Edit Book</h4>
        </div>

        <div class="card-body">

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_book.php">

                <input
                    type="hidden"
                    name="id"
                    value="<?= e($book['id']) ?>"
                >

                <div class="mb-3">
                    <label class="form-label">ISBN</label>

                    <input
                        type="text"
                        name="isbn"
                        class="form-control"
                        value="<?= e($book['isbn']) ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Book Title</label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        value="<?= e($book['title']) ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Author</label>

                    <input
                        type="text"
                        name="author"
                        class="form-control"
                        value="<?= e($book['author']) ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Genre</label>

                    <input
                        type="text"
                        name="genre"
                        class="form-control"
                        value="<?= e($book['genre']) ?>"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Publication Year</label>

                    <input
                        type="number"
                        name="publication_year"
                        class="form-control"
                        min="1000"
                        max="2155"
                        value="<?= e($book['publication_year']) ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Quantity</label>

                    <input
                        type="number"
                        name="quantity"
                        class="form-control"
                        min="0"
                        value="<?= e($book['quantity']) ?>"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary">
                    Update Book
                </button>

                <a href="index.php" class="btn btn-secondary">
                    Cancel
                </a>

            </form>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>