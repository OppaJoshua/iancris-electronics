<?php
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['ids']) || empty($_GET['ids'])) {
    echo json_encode([]);
    exit();
}

$ids = explode(',', $_GET['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

$sql = "SELECT id, name, description, image, images, category FROM products WHERE id IN ($placeholders) AND status = 'active'";
$stmt = $conn->prepare($sql);

$types = str_repeat('i', count($ids));
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    // Parse images JSON
    $images = json_decode($row['images'], true);
    if (!$images || !is_array($images)) {
        $images = $row['image'] ? [$row['image']] : [];
    }
    $row['images_array'] = $images;
    $products[] = $row;
}

echo json_encode($products);
?>
