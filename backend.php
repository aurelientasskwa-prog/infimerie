<?php
// Configuration de la connexion
$host = "localhost";    
$dbname = "infirmerie_scolaire"; 
$username = "root";  
$password = ""; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}



if (isset($_POST['terminer'])) {
    $motif = $_POST['motif'];
    $diagnostic = $_POST['diagnostic'];
    $traitement = $_POST['traitement'];
    $idEtudiant = $_POST['idEtudiant'] ?? null;   //  ID caché
    $date = $_POST['date'];
    $idInfirmiere = $_POST['medicament'];

    if (empty($idEtudiant)) {
        die("Erreur : aucun étudiant sélectionné. Faites une recherche avant d’enregistrer.");
    }

    $sql = "INSERT INTO consultation (motif, diagnostic, traitement, idEtudiant, dateConsultation, idInfirmiere) 
            VALUES (:motif, :diagnostic, :traitement, :idEtudiant, :dateConsultation, :idInfirmiere)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':motif' => $motif,
        ':diagnostic' => $diagnostic,
        ':traitement' => $traitement,
        ':idEtudiant' => $idEtudiant,
        ':dateConsultation' => $date,
        ':idInfirmiere' => $idInfirmiere
    ]);

    echo "Consultation enregistrée avec succès.";
}

if (isset($_POST['search'])) {
    $nom = $_POST['search'];

    $sql = "SELECT idEtudiant, nom, prenom, classe FROM etudiant WHERE nom LIKE :nom LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':nom' => "%$nom%"]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode($result);
    } else {
        echo "";
    }
    exit;
}

?>