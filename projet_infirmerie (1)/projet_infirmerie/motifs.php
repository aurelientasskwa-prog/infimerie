<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$sql = "SELECT DISTINCT motif FROM consultation ORDER BY motif ASC";
$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));