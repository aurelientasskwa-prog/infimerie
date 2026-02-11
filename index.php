<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter un étudiant</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,400;1,400&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="wrapper">

    <div class="header">
        <a href="#" class="retour">
            <img src="images/fleche.jpg" alt="retour" class="icone-retour">
            <span>RETOUR</span>
        </a>


        <div class="center-title">
            <div class="avatar">
                <img src="images/avatar.jpg" alt="avatar">
            </div>
            <h2>Ajouter un etudiant</h2>
        </div>

        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <div class="menu" id="menu">
        <a href="#"></a>
        <a href="#"></a>
        <a href="#"></a>
        <a href="#"></a>
    </div>

    <div class="form-container">
        <form id="formEtudiant">

            <div class="grid">

                <div class="col">
                    <label>Matricule:</label>
                    <input type="text" name="matricule" required>

                    <label>Nom et Prénom:</label>
                    <input type="text" name="nom" required>

                    <label>Date de naissance:</label>
                    <input type="date" name="dateNaiss" required>

                    <label>Sexe:</label>
                    <select name="sexe" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="Masculin">Masculin</option>
                        <option value="Féminin">Féminin</option>
                    </select>
                </div>

                <div class="col">
                    <label>Filière:</label>
                    <input type="text" name="classe" required>

                    <label>Adresse:</label>
                    <input type="text" name="adresse" required>

                    <label>Contact:</label>
                    <input type="text" name="contact" required>
                </div>

            </div>

            <div class="buttons">
                <button type="submit" class="terminer">Terminer</button>
                <button type="reset" class="annuler">Annuler</button>
            </div>

        </form>

        <p id="message"></p>
    </div>

</div>

<script src="script.js"></script>
</body>
</html>
