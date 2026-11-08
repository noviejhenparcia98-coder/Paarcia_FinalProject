<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: index.php?msg=" . urlencode("Invalid book ID."));
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $message = "Book deleted successfully!";
    } else {
        $message = "Book not found.";
    }

    header("Location: index.php?msg=" . urlencode($message));
    exit;

} catch (PDOException $e) {
    error_log("Delete error: " . $e->getMessage());

    header("Location: index.php?msg=" . urlencode("Delete failed. Please check the database."));
    exit;
}