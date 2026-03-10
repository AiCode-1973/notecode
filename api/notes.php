<?php
require_once '../db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $filter = $_GET['filter'] ?? 'all';
        $query = "SELECT * FROM notes WHERE user_id = ? ";
        if ($filter === 'favorites') {
            $query .= "AND is_favorite = 1 ";
        }
        $query .= "ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'create':
        $data = json_decode(file_get_contents('php://input'), true);
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');

        if (empty($title)) {
            echo json_encode(['error' => 'Title is required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO notes (user_id, title, description) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, $description]);
        echo json_encode(['id' => $pdo->lastInsertId(), 'status' => 'success']);
        break;

    case 'update':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';

        if (!$id) {
            echo json_encode(['error' => 'ID is required']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE notes SET title = ?, description = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $description, $id, $user_id]);
        echo json_encode(['status' => 'success']);
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'ID is required']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        echo json_encode(['status' => 'success']);
        break;

    case 'toggle_fav':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['error' => 'ID is required']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE notes SET is_favorite = 1 - is_favorite WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $user_id]);
        
        // Return new status
        $stmt = $pdo->prepare("SELECT is_favorite FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['is_favorite' => $stmt->fetchColumn()]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
?>
