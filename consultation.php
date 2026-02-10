<?php include 'backend.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de consultation</title>
    <style>
.form-header {
  display: flex;
  justify-content: space-between; 
  align-items: center;
  padding: 10px 20px;
  background-color: #FFFFFF;
}

.header-item img {
  height: 30px;
  width: auto;
}
 
.center img {
  height: 80px; 
}


.retour {
  display: flex;
  align-items: center;
  gap: 8px;
}

.retour-text {
  color: #539EFF; 
  font-weight: bold;
  text-transform: uppercase;
  font-size: 20px;
}


.form-title {
  text-align: center;
  color: #539EFF;
  font-size: 30px;
  margin: 15px 0;
  font-weight: 700;
}


.search-container {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 30px auto;
  width: 80%;
  position: relative;
}

.search-input {
  width: 100%;
  padding: 20px 25px 10px 10px;
  border: 3px solid #539EFF;  
  border-radius: 10px;
  font-size: 16px;
  font-style: italic;
  font-weight: 500;
  color: #000000;
  outline: none;
  box-sizing: border-box;
  transition: all 0.3s ease;    
}

.search-input:focus {
  border-color: #0056b3;        
  box-shadow: 0 0 6px rgba(0, 91, 187, 0.5); 
}

.search-icon {
  position: absolute;
  right: 12px;
  height: 40px;
  width: 40px;
  pointer-events: none; 
}

.search-input::placeholder {
  font-style: italic;
  font-weight: 500;
  color: #000;
}

.search-button {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}

.search-button img {
  height: 40px;
  width: 40px;
}

.form-body {
  display: grid;
  grid-template-columns: 1fr 1fr; 
  gap: 30px;
  width: 80%;
  margin: 0 auto;
}

.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
}

.form-group label {
  font-style: italic;   
  font-weight: medium;   
  margin-bottom: 6px;
  color: #000000;
  font-size: 18px;
}

.form-group input {
  padding: 20px;
  border-radius: 6px;
  border: none; 
  background-color: #F6FAFE; 
  font-size: 15px;
  box-shadow: 0 4px 4px rgba(0,0,0,0.25); 
}

.form-footer {
  display: flex;
  justify-content: center; 
  gap: 20px;               
  margin-left: 500px;
}


.btn {
  padding: 12px 50px;
  border-radius: 25px;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
}


.btn-finish {
  font-size: 18px;
  background-color: #539EFF;   
  color: #FFFFFF;                
  border: none;                
  box-shadow: 0 4px 4px rgba(0,0,0,0.25); 
}


.btn-cancel {
  font-size: 20px;
  background-color: #FFFFFF;     
  color: #539EFF;              
  border: 2px solid #539EFF;   
  box-shadow: none;            
}


</style>
</head>
<body>
<div class="form-container">

<header class="form-header">
  <!-- Bloc retour -->
  <div class="header-item retour">
    <img src="retour.png" alt="Retour">
    <span class="retour-text">RETOUR</span>
  </div>

  <!-- Icône centrale -->
  <div class="header-item center">
    <img src="consultation.png" alt="Consultation">
  </div>

  <!-- Menu hamburger -->
  <div class="header-item menu">
    <img src="menu.png" alt="Menu">
  </div>
</header>

<!-- Titre -->
<h1 class="form-title">FORMULAIRE DE CONSULTATION</h1>

<!-- Barre de recherche -->
<div class="search-container">
  <input type="text" placeholder="Rechercher un étudiant......" class="search-input">
  <button class="search-button">
    <img src="search.png" alt="Recherche">
  </button>
</div>

<form method="POST">
<main class="form-body">
  <!-- Colonne gauche -->
  <div class="column left">
    <div class="form-group">
      <label for="motif">Motif de consultation:</label>
      <input type="text" id="motif" name="motif">
    </div>

    <div class="form-group">
      <label for="diagnostic">Diagnostic:</label>
      <input type="text" id="diagnostic" name="diagnostic">
    </div>

    <div class="form-group">
      <label for="traitement">Traitement:</label>
      <input type="text" id="traitement" name="traitement">
    </div>
  </div>

  <!-- Colonne droite -->
  <div class="column right">
    <div class="form-group">
      <label for="etudiant">Étudiant:</label>
      <input type="text" id="etudiant" name="etudiant">
      <input type="hidden" id="idEtudiant" name="idEtudiant"><!-- champ caché -->

    </div>

    <div class="form-group">
      <label for="date">Date:</label>
      <input type="date" id="date" name="date">
    </div>

    <div class="form-group">
      <label for="medicament">Infirmière:</label>
      <input type="text" id="medicament" name="medicament">
    </div>
  </div>
</main>

<footer class="form-footer">
  <button type="submit" class="btn btn-finish" name="terminer">Terminer</button>
  <button type="reset" class="btn btn-cancel" name="annuler">Annuler</button>
</footer>
</form>

  <!-- Script pour la recherche -->
   <script>
document.querySelector('.search-button').addEventListener('click', function() {
    const searchValue = document.querySelector('.search-input').value;

    fetch('backend.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'search=' + encodeURIComponent(searchValue)
    })
    .then(response => response.json())
    .then(data => {
        if (data) {
            // Champ visible
            document.getElementById('etudiant').value = data.nom + " " + data.prenom + " - " + data.classe;
            // Champ caché
            document.getElementById('idEtudiant').value = data.idEtudiant;
        } else {
            alert("Étudiant non trouvé");
        }
    });
});
</script>

</body>
</html>