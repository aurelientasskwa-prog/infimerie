
<?php
require_once 'config.php';

// ── Récupération de la consultation ──
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$consultation = null;
if ($id > 0) {
  $stmt = $pdo->prepare("
    SELECT
      c.idConsultation,
      c.dateConsultation,
      c.motif,
      c.diagnostic,
      c.traitement,
      e.nom AS etudiantNom,
      e.prenom AS etudiantPrenom,
      e.matricule,
      e.classe,
      i.nom AS infirmiereNom,
      i.prenom AS infirmierePrenom
    FROM consultation c
    JOIN etudiant e ON c.idEtudiant = e.idEtudiant
    JOIN infirmiere i ON c.idInfirmiere = i.idInfirmiere
    WHERE c.idConsultation = :id
  ");
  $stmt->execute([':id' => $id]);
  $consultation = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ── Traitement de la modification ──
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enregistrer'])) {
  try {
    $stmt = $pdo->prepare("
      UPDATE consultation
      SET
        dateConsultation = :date,
        motif            = :motif,
        diagnostic       = :diagnostic,
        traitement       = :traitement
      WHERE idConsultation = :id
    ");
    $stmt->execute([
      ':date'        => $_POST['dateConsultation'],
      ':motif'       => $_POST['motif'],
      ':diagnostic'  => $_POST['diagnostic'],
      ':traitement'  => $_POST['traitement'],
      ':id'          => $id
    ]);
    $message     = 'Consultation mise à jour avec succès';
    $messageType = 'succes';

    // Recharger les données après mise à jour
    $stmt = $pdo->prepare("
      SELECT
        c.idConsultation,
        c.dateConsultation,
        c.motif,
        c.diagnostic,
        c.traitement,
        e.nom AS etudiantNom,
        e.prenom AS etudiantPrenom,
        e.matricule,
        e.classe,
        i.nom AS infirmiereNom,
        i.prenom AS infirmierePrenom
      FROM consultation c
      JOIN etudiant e ON c.idEtudiant = e.idEtudiant
      JOIN infirmiere i ON c.idInfirmiere = i.idInfirmiere
      WHERE c.idConsultation = :id
    ");
    $stmt->execute([':id' => $id]);
    $consultation = $stmt->fetch(PDO::FETCH_ASSOC);

  } catch (Exception $e) {
    $message     = 'Erreur lors de la mise à jour';
    $messageType = 'erreur';
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Détail consultation</title>
  <style>
    /* ── Header ── */
  header {
  display: flex;
  justify-content: space-between; 
  align-items: center;
  padding: 10px 20px;
  background-color: #FFFFFF;
    }

    .btn-back {
      display: flex;
      align-items: center;
      gap: 8px;
      background: none;
      border: none;
      cursor: pointer;
      color: #539EFF;
      font-weight: bold;
      font-size: 20px;
  
    }

    .header-center {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }

    .header-center img {
      width: 80px;  
      height: 80px; 
    }

    .header-title {
  text-align: center;
  color: #539EFF;
  font-size: 30px;
  margin: 15px 0;
  font-weight: 700;
    }

    .btn-menu {
      background: none;
      border: none;
      cursor: pointer;
    }

    .form-title {
  text-align: center;
  color: #539EFF;
  font-size: 30px;
  margin: 15px 0;
  font-weight: 700;
    }

    .btn-menu img {
      width: 30px;  
      height: 30px; 
    }

    .btn-back img {
      width: 22px; 
      height: 22px; 
    }

    /* ── Bandeau message ── */
    .bandeau {
      display: none;
      padding: 14px 20px;
      font-weight: 600;
      font-size: 14px;
      text-align: center;
    }
    .bandeau.succes { background: #dcfce7; color: #16a34a; display: block; }
    .bandeau.erreur { background: #fee2e2; color: #dc2626; display: block; }

    /* ── Bandeau mode édition ── */
    .bandeau-edition {
      display: none;
      background: #fffbeb;
      color: #d97706;
      padding: 10px 20px;
      font-size: 13px;
      font-weight: 600;
      text-align: center;
      border-bottom: 2px solid #fcd34d;
    }
    .bandeau-edition.actif { display: block; }

    /* ── Contenu ── */
    .page {
      max-width: 1200px;
      margin: 24px auto;
      padding: 0 16px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    /* ── Carte date ── */
    .carte-date {
      background: var(--bleu);
      color: white;
      border-radius: 12px;
      padding: 18px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .carte-date img {
      width: 28px;  /* modifiable */
      height: 28px; /* modifiable */
      filter: brightness(0) invert(1);
    }

    .carte-date .label {
      font-size: 12px;
      opacity: .8;
      margin-bottom: 4px;
    }

    .carte-date .valeur {
      font-size: 18px;
      font-weight: 700;
    }

    /* ── Blocs ── */
    .bloc {
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    }

    .bloc-etudiant {
     border-radius: 12px;
      padding: 20px;
      box-shadow: 0 1px 6px rgba(0,0,0,0.06); 
      background-color:rgba(83, 158, 255, 0.2) ;
      margin-top: -35px; 
    }
    .bloc-infirmiere{

     border-radius: 12px;
      padding: 20px;
      box-shadow: 0 1px 6px rgba(0,0,0,0.06); 
      background-color:rgba(96, 163, 251, 0.2) ; 
    }


    .bloc-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 16px;
    }

    .bloc-titre {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 18px;
      font-weight: 700;
      color: var(--gris);
      text-transform: uppercase;
      letter-spacing: .5px;
    }

    .bloc-titre img {
      width: 30px;  
      height: 30px; 
    }

    /* ── Infos fixes (étudiant, infirmière) ── */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      
    }

    .info-item .label {
     font-style: italic;   
     font-weight: medium; 
      font-size: 20px;
      color: var(--gris);
      margin-bottom: 3px;
    }

    .info-item .valeur {
    font-size: 18px;
    font-weight: 800;
    font-style: italic;   
    font-weight: medium; 
    }

    /* ── Champs consultation ── */
    .champ {
      margin-bottom: 16px;
    }

    .champ:last-child { margin-bottom: 0; }

    .champ label {
      display: block;
      font-size: 18px;
      color: var(--gris);
      font-weight: 800;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: .4px;
          font-style: italic;   
    font-weight: medium; 
    }

    /* Mode lecture */
    .champ .lecture {
  padding: 20px;
  border-radius: 6px;
  border: none; 
  background-color: #F6FAFE; 
  font-size: 15px;
  box-shadow: 0 4px 4px rgba(0,0,0,0.25); 
    }

    /* Mode édition */
    .champ textarea {
      width: 100%;
      background: #F6FAFE;
      border: 2px solid #EBEBEB;
      border-radius: 8px;
      padding: 12px 14px;
      font-size: 14px;
      font-family: Arial, sans-serif;
      line-height: 1.5;
      resize: vertical;
      min-height: 80px;
      display: none;
      outline: none;
    }

    .champ input[type="date"] {
      width: 100%;
      background: #F6FAFE;
      border: 2px solid #EBEBEB;
      border-radius: 8px;
      padding: 12px 14px;
      font-size: 18px;
      font-family: Arial, sans-serif;
      display: none;
      outline: none;
    }

    /* ── Bouton Modifier ── */
    .btn-modifier {
  font-size: 16px;
  background-color: #539EFF;   
  color: #FFFFFF;                
  border: none;                
  box-shadow: 0 4px 4px rgba(0,0,0,0.25); 
padding: 12px 50px;
  border-radius: 25px;
  font-weight: bold;
  cursor: pointer;
    }


    /* ── Boutons bas de page ── */
    .actions-bas {
      display: none;
      gap: 12px;
      margin-top: 4px;
      margin-left: 300px;
    }

    .actions-bas.actif { display: flex; }

    .btn-enregistrer {
  font-size: 16px;
  background-color: #539EFF;   
  color: #FFFFFF;                
  border: none;                
  box-shadow: 0 4px 4px rgba(0,0,0,0.25); 
padding: 12px 50px;
  border-radius: 25px;
  font-weight: bold;
  cursor: pointer;
width: 300px;
height: 50px;
    }

    .btn-enregistrer:hover { opacity: .9; }

    .btn-annuler {
  font-size: 20px;
  background-color: #FFFFFF;     
  color: #539EFF;              
  border: 2px solid #539EFF;   
  box-shadow: none;
  border-radius: 25px; 
  width: 300px;
  height: 50px;
    }

    .btn-annuler:hover { background: #fee2e2; }

    /* ── Popup confirmation ── */
    .overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.4);
      z-index: 200;
      align-items: center;
      justify-content: center;
    }

    .overlay.actif { display: flex; }

    .popup {
      background: white;
      border-radius: 14px;
      padding: 28px 24px;
      max-width: 320px;
      width: 90%;
      text-align: center;
    }

    .popup h3 {
      font-size: 17px;
      margin-bottom: 10px;
    }

    .popup p {
      font-size: 14px;
      color: var(--gris);
      margin-bottom: 24px;
    }

    .popup-btns {
      display: flex;
      gap: 10px;
    }

    .popup-btns button {
      flex: 1;
      padding: 12px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
    }

    .btn-confirmer {
      background: var(--bleu);
      color: white;
      border: none;
    }

    .btn-annuler-popup {
      background: white;
      color: var(--rouge);
      border: 2px solid var(--rouge);
    }
  </style>
</head>
<body>

<?php if ($message): ?>
<div class="bandeau <?= $messageType ?>" id="bandeau-msg">
  <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- ── Header ── -->
<header class="form-header">
  <button class="btn-back" onclick="window.location.href='listeConsultation.html'">
    <img src="retour.png" alt="Retour">
    RETOUR
  </button>

  <div class="header-center">
    <img src="detailConsultation.png" alt="Consultation">
  </div>

  <button class="btn-menu">
    <img src="menu.png" alt="Menu">
  </button>
</header>

<h1 class="form-title">DETAIL CONSULTATION</h1>

<?php if (!$consultation): ?>
  <div style="text-align:center;padding:40px;color:#999">Consultation introuvable.</div>
<?php else: ?>

<!-- ── Page ── -->
<div class="page">

  <!-- Carte date -->
  <div class="carte-date">
    <img src="calendar.png" alt="Date">
    <div>
      <div class="label">Date de la consultation</div>
      <div class="valeur" id="affichage-date">
        <?= date('d F Y', strtotime($consultation['dateConsultation'])) ?>
      </div>
    </div>
  </div>

  <!-- Bloc étudiant -->
  <div class="bloc-etudiant">
    <div class="bloc-header">
      <div class="bloc-titre">
        <img src="etudiant.png" alt="Étudiant">
        Étudiant
      </div>
    </div>
    <div class="info-grid">
      <div class="info-item">
        <div class="label">Nom complet</div>
        <div class="valeur"><?= htmlspecialchars($consultation['etudiantPrenom'] . ' ' . $consultation['etudiantNom']) ?></div>
      </div>
      <div class="info-item">
        <div class="label">Matricule</div>
        <div class="valeur"><?= htmlspecialchars($consultation['matricule']) ?></div>
      </div>
      <div class="info-item">
        <div class="label">Classe</div>
        <div class="valeur"><?= htmlspecialchars($consultation['classe']) ?></div>
      </div>
    </div>
  </div>

  <!-- Bloc consultation -->
  <form method="POST">
  <input type="hidden" name="enregistrer" value="1">

  <div class="bloc">
    <div class="bloc-header">
      <div class="bloc-titre">
        <img src="consultation.png" alt="Consultation">
        Consultation
      </div>
      <button type="button" class="btn-modifier" id="btn-modifier" onclick="activerEdition()">
        Modifier
      </button>
    </div>

    <!-- Date -->
    <div class="champ">
      <label>Date</label>
      <div class="lecture" id="lecture-date">
        <?= date('d/m/Y', strtotime($consultation['dateConsultation'])) ?>
      </div>
      <input
        type="date"
        name="dateConsultation"
        id="input-date"
        value="<?= htmlspecialchars($consultation['dateConsultation']) ?>"
      >
    </div>

    <!-- Motif -->
    <div class="champ">
      <label>Motif</label>
      <div class="lecture" id="lecture-motif">
        <?= htmlspecialchars($consultation['motif']) ?>
      </div>
      <textarea name="motif" id="input-motif"><?= htmlspecialchars($consultation['motif']) ?></textarea>
    </div>

    <!-- Diagnostic -->
    <div class="champ">
      <label>Diagnostic</label>
      <div class="lecture" id="lecture-diagnostic">
        <?= htmlspecialchars($consultation['diagnostic']) ?>
      </div>
      <textarea name="diagnostic" id="input-diagnostic"><?= htmlspecialchars($consultation['diagnostic']) ?></textarea>
    </div>

    <!-- Traitement -->
    <div class="champ">
      <label>Traitement</label>
      <div class="lecture" id="lecture-traitement">
        <?= htmlspecialchars($consultation['traitement']) ?>
      </div>
      <textarea name="traitement" id="input-traitement"><?= htmlspecialchars($consultation['traitement']) ?></textarea>
    </div>

  </div>

  <!-- Boutons bas -->
  <div class="actions-bas" id="actions-bas">
    <button type="button" class="btn-annuler" onclick="annulerEdition()">Annuler</button>
    <button type="button" class="btn-enregistrer" onclick="demanderConfirmation()">Enregistrer</button>
  </div>

  <!-- Bouton submit caché déclenché par JS -->
  <button type="submit" id="btn-submit-hidden" style="display:none"></button>

  </form>

  <!-- Bloc infirmière -->
  <div class="bloc-infirmiere">
    <div class="bloc-header">
      <div class="bloc-titre">
        <img src="img2.png" alt="Infirmière">
        Infirmière
      </div>
    </div>
    <div class="info-item">
      <div class="label">Nom complet</div>
      <div class="valeur"><?= htmlspecialchars($consultation['infirmierePrenom'] . ' ' . $consultation['infirmiereNom']) ?></div>
    </div>
  </div>

</div>

<!-- ── Popup confirmation ── -->
<div class="overlay" id="overlay">
  <div class="popup">
    <h3>Confirmer la modification</h3>
    <p>Voulez-vous enregistrer les modifications apportées à cette consultation ?</p>
    <div class="popup-btns">
      <button class="btn-annuler-popup" onclick="fermerPopup()">Annuler</button>
      <button class="btn-confirmer" onclick="confirmerEnregistrement()">Enregistrer</button>
    </div>
  </div>
</div>

<?php endif; ?>

<script>
  // Masquer le bandeau succès après 3 secondes
  const bandeauMsg = document.getElementById('bandeau-msg');
  if (bandeauMsg) {
    setTimeout(() => {
      bandeauMsg.style.transition = 'opacity .5s';
      bandeauMsg.style.opacity = '0';
      setTimeout(() => bandeauMsg.remove(), 500);
    }, 3000);
  }

  function activerEdition() {
    // Afficher inputs, cacher lectures
    ['date', 'motif', 'diagnostic', 'traitement'].forEach(champ => {
      document.getElementById('lecture-' + champ).style.display = 'none';
      document.getElementById('input-' + champ).style.display = 'block';
    });

    document.getElementById('actions-bas').classList.add('actif');
    document.getElementById('bandeau-edition').classList.add('actif');
    document.getElementById('btn-modifier').style.display = 'none';
  }

  function annulerEdition() {
    // Remettre les lectures, cacher inputs
    ['date', 'motif', 'diagnostic', 'traitement'].forEach(champ => {
      document.getElementById('lecture-' + champ).style.display = 'block';
      document.getElementById('input-' + champ).style.display = 'none';
    });

    document.getElementById('actions-bas').classList.remove('actif');
    document.getElementById('bandeau-edition').classList.remove('actif');
    document.getElementById('btn-modifier').style.display = 'inline-block';
  }

  function demanderConfirmation() {
    document.getElementById('overlay').classList.add('actif');
  }

  function fermerPopup() {
    document.getElementById('overlay').classList.remove('actif');
  }

  function confirmerEnregistrement() {
    fermerPopup();
    document.getElementById('btn-submit-hidden').click();
  }
</script>

</body>
</html>
