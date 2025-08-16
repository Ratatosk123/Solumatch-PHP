<?php
// solumatch_atualizado/PHP/get_profile_data.php
header('Content-Type: application/json');
session_start();
require_once '../config.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit();
}

$loggedInUserId = $_SESSION['user_id'];
$type = $_GET['type'] ?? ''; 
$itemId = $_GET['id'] ?? null;

try {
    if ($type === 'experiences') {
        if ($itemId) {
            $stmt = $pdo->prepare("SELECT * FROM experiencias WHERE id = :id AND usuario_id = :user_id");
            $stmt->execute([':id' => $itemId, ':user_id' => $loggedInUserId]);
            $response['item'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM experiencias WHERE usuario_id = :user_id ORDER BY data_inicio DESC");
            $stmt->execute([':user_id' => $loggedInUserId]);
            $response['experiences'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $response['success'] = true;
    } 
    elseif ($type === 'educations') { 
        if ($itemId) {
            $stmt = $pdo->prepare("SELECT * FROM educacao WHERE id = :id AND usuario_id = :user_id");
            $stmt->execute([':id' => $itemId, ':user_id' => $loggedInUserId]);
            $response['item'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM educacao WHERE usuario_id = :user_id ORDER BY data_inicio DESC");
            $stmt->execute([':user_id' => $loggedInUserId]);
     
            $response['educations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $response['success'] = true;
    } else {
        $response['message'] = 'Tipo de dado inválido.';
    }

} catch (PDOException $e) {
    $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
    error_log("Erro no get_profile_data.php: " . $e->getMessage());
}

echo json_encode($response);
?>