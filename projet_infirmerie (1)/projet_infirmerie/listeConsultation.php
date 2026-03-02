<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$where = [];
$params = [];


// Filtre par classe (filière)
if (!empty($_GET['filiere'])) {
    $where[] = "e.classe = :classe";
    $params[':classe'] = $_GET['filiere'];
}

// Filtre par étudiant (nom ou prénom)
if (!empty($_GET['etudiant'])) {
    $where[] = "LOWER(CONCAT(e.prenom,' ',e.nom)) LIKE :etudiant";
    $params[':etudiant'] = "%".strtolower($_GET['etudiant'])."%";
}

// Filtre par date
if (!empty($_GET['date'])) {
    $where[] = "DATE(c.dateConsultation) = :date";
    $params[':date'] = $_GET['date'];
}

// Filtre par motif
if (!empty($_GET['motif'])) {
    $where[] = "c.motif = :motif";
    $params[':motif'] = $_GET['motif'];
}

$sql = "SELECT c.idConsultation, c.dateConsultation, c.motif, c.diagnostic, c.traitement,
               CONCAT(e.prenom,' ',e.nom) AS etudiant, e.classe AS filiere,
               CONCAT(i.prenom,' ',i.nom) AS infirmiere
        FROM consultation c
        JOIN etudiant e ON c.idEtudiant = e.idEtudiant
        JOIN infirmiere i ON c.idInfirmiere = i.idInfirmiere";

if ($where) {
    $sql .= " WHERE ".implode(" AND ", $where);
}
$sql .= " ORDER BY c.dateConsultation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));