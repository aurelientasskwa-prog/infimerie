<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $matricule = $_POST['matricule'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $dateNaiss = $_POST['dateNaiss'] ?? '';
    $sexe = $_POST['sexe'] ?? '';
    $classe = $_POST['classe'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $contact = $_POST['contact'] ?? '';

    $conn = new mysqli("localhost","root","","infirmerie_scolaire");

    if($conn->connect_error){
        die("Erreur de connexion");
    }

    $sql = "INSERT INTO etudiant 
    (matricule, nom, dateNaiss, sexe, classe, adresse, contact)
    VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss",
        $matricule, $nom, $dateNaiss,
        $sexe, $classe, $adresse, $contact
    );

    echo $stmt->execute() 
        ? "Étudiant ajouté avec succès ✅"
        : "Erreur lors de l'ajout ❌";

    $stmt->close();
    $conn->close();
}
?>
