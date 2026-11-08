```php
<?php
require 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn = trim($_POST['isbn'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $year = $_POST['publication_year'] ?? '';
    $quantity = $_POST['quantity'] ?? '';

    if (
        $isbn === '' || $title === '' || $author === '' ||
        $genre === '' || $year === '' || $quantity === ''
    ) {
        $error = "Please fill in all fields.";
    } elseif (
        !filter_var($year, FILTER_VALIDATE_INT) ||
        $year < 1000 || $year > 2155
    ) {
        $error = "Enter a valid publication year.";
    } elseif (
        !filter_var($quantity, FILTER_VALIDATE_INT) ||
        $quantity < 0
    ) {
        $error = "Quantity must be zero or greater.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO books
                (isbn, title, author, genre, publication_year, quantity)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $isbn, $title, $author, $genre, $year, $quantity
            ]);

            header("Location: index.php?msg=" . urlencode("Book added successfully!"));
            exit;

        } catch (PDOException $e) {
            $error = "Unable to add book. ISBN may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Add Book | Library Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="style.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #0b1f3a, #123b69);
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #071629;
            border-bottom: 3px solid #F4BE38;
        }

        .navbar-brand {
            color: #F4BE38 !important;
            font-size: 22px;
        }

        .form-card {
            max-width: 650px;
            background: #123b69;
            border: 1px solid #F4BE38;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }

        .form-card .card-header {
            background: #071629;
            color: #F4BE38;
            padding: 20px 25px;
            border-bottom: 2px solid #F4BE38;
        }

        .form-card .card-header h4 {
            margin: 0;
            font-weight: bold;
        }

        .form-card .card-body {
            padding: 30px;
            color: #ffffff;
        }

        .form-label {
            color: #F4BE38;
            font-weight: 600;
        }

        .form-control {
            background-color: #0b1f3a;
            color: #ffffff;
            border: 1px solid #52769e;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .form-control:focus {
            background-color: #0b1f3a;
            color: #ffffff;
            border-color: #F4BE38;
            box-shadow: 0 0 0 0.2rem rgba(244, 190, 56, 0.25);
        }

        .form-control::placeholder {
            color: #b8c7d9;
        }

        .btn-save {
            background-color: #F4BE38;
            color: #071629;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            padding: 10px 22px;
        }

        .btn-save:hover {
            background-color: #ffd45c;
            color: #071629;
        }

        .btn-cancel {
            background-color: #345678;
            color: #ffffff;
            border: 1px solid #6c8bad;
            border-radius: 8px;
            padding: 10px 22px;
        }

        .btn-cancel:hover {
            background-color: #456b91;
            color: #ffffff;
        }

        .back-link {
            color: #F4BE38;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            color: #ffffff;
        }

        .alert-danger {
            border-radius: 8px;
        }
    </style>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            📚 Library Management
        </a>

        <a href="index.php" class="back-link">
            ← Back to Dashboard
        </a>
    </div>
</nav>

<!-- Add Book Form -->
<div class="container py-5">

    <div class="card form-card mx-auto">

        <div class="card-header">
            <h4>📖 Add New Book</h4>
            <small class="text-light">
                Enter the book information below
            </small>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">ISBN</label>
                    <input
                        type="text"
                        name="isbn"
                        class="form-control"
                        maxlength="20"
                        placeholder="Enter ISBN"
                        value="<?= htmlspecialchars($_POST['isbn'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Book Title</label>
                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        maxlength="150"
                        placeholder="Enter book title"
                        value="<?= htmlspecialchars($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Author</label>
                    <input
                        type="text"
                        name="author"
                        class="form-control"
                        maxlength="100"
                        placeholder="Enter author name"
                        value="<?= htmlspecialchars($_POST['author'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Genre</label>
                    <input
                        type="text"
                        name="genre"
                        class="form-control"
                        maxlength="50"
                        placeholder="Enter book genre"
                        value="<?= htmlspecialchars($_POST['genre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Publication Year</label>
                    <input
                        type="number"
                        name="publication_year"
                        class="form-control"
                        min="1000"
                        max="2155"
                        placeholder="Enter publication year"
                        value="<?= htmlspecialchars($_POST['publication_year'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Quantity</label>
                    <input
                        type="number"
                        name="quantity"
                        class="form-control"
                        min="0"
                        placeholder="Enter number of copies"
                        value="<?= htmlspecialchars($_POST['quantity'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        required>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-save">
                        💾 Save Book
                    </button>

                    <a href="index.php" class="btn btn-cancel">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

    <p class="text-center mt-4">
        <small class="text-light">
            Library Management System
        </small>
    </p>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
```