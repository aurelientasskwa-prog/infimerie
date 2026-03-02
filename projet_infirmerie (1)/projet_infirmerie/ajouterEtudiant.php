<?php
session_start();
require_once 'config.php'; // 

// Récupération et nettoyage des données du formulaire
$matricule     = trim($_POST['matricule'] ?? '');
$nom           = trim($_POST['nom'] ?? '');
$prenom        = trim($_POST['prenom'] ?? '');
$sexe          = trim($_POST['sexe'] ?? '');
$classe        = trim($_POST['classe'] ?? '');
$nomInfirmiere = trim($_POST['nomInfirmiere'] ?? '');

// Validation des champs obligatoires
if (empty($matricule) || empty($nom) || empty($prenom) || empty($nomInfirmiere)) {
    die(json_encode(["status" => "error", "message" => "Veuillez remplir tous les champs obligatoires."]));
}

// Étape 1 : Récupérer l'idInfirmiere à partir du nom saisi
$sqlInf = "SELECT idInfirmiere FROM infirmiere WHERE nom = ?";
$stmtInf = $pdo->prepare($sqlInf);
$stmtInf->execute([$nomInfirmiere]);
$infirmiere = $stmtInf->fetch(PDO::FETCH_ASSOC);

if (!$infirmiere) {
    die(json_encode(["status" => "error", "message" => "Aucune infirmière trouvée avec le nom : " . htmlspecialchars($nomInfirmiere)]));
}

$idInfirmiere = $infirmiere['idInfirmiere'];

// Étape 2 : Insérer l'étudiant avec l'idInfirmiere récupéré
$sqlEtud = "INSERT INTO etudiant (matricule, nom, prenom, sexe, classe, idInfirmiere)
            VALUES (?, ?, ?, ?, ?, ?)";
$stmtEtud = $pdo->prepare($sqlEtud);

try {
    $stmtEtud->execute([$matricule, $nom, $prenom, $sexe, $classe, $idInfirmiere]);
    echo json_encode(["status" => "success", "message" => "Étudiant ajouté avec succès."]);
} catch (PDOException $e) {
    if ($e->getCode() == "23000") { // doublon (clé unique)
        echo json_encode(["status" => "error", "message" => "Ce matricule existe déjà."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur lors de l'ajout : " . $e->getMessage()]);
    }
}
?>