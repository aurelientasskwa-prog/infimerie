<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-t');

// KPIs
$totalEleves = (int)$pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM consultation WHERE dateConsultation = CURDATE()");
$stmt->execute();
$consultationsToday = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM consultation WHERE dateConsultation BETWEEN :from AND :to");
$stmt->execute([':from' => $from, ':to' => $to]);
$consultationsPeriode = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
  SELECT COUNT(*)
  FROM consultation
  WHERE dateConsultation BETWEEN :from AND :to
    AND (traitement IS NULL OR traitement = '')
");
$stmt->execute([':from' => $from, ':to' => $to]);
$enAttente = (int)$stmt->fetchColumn();

// Top motifs
$stmt = $pdo->prepare("
  SELECT COALESCE(NULLIF(TRIM(motif), ''), 'Non précisé') AS motifLabel, COUNT(*) AS cnt
  FROM consultation
  WHERE dateConsultation BETWEEN :from AND :to
  GROUP BY motifLabel
  ORDER BY cnt DESC
  LIMIT 5
");
$stmt->execute([':from' => $from, ':to' => $to]);
$topMotifs = $stmt->fetchAll();

// Détails consultations (limite 200)
$stmt = $pdo->prepare("
  SELECT
    c.dateConsultation, c.motif, c.diagnostic, c.traitement,
    e.matricule, e.nom, e.prenom, e.classe,
    i.nom AS infirmiereNom, i.prenom AS infirmierePrenom
  FROM consultation c
  JOIN etudiant e ON e.idEtudiant = c.idEtudiant
  JOIN infirmiere i ON i.idInfirmiere = c.idInfirmiere
  WHERE c.dateConsultation BETWEEN :from AND :to
  ORDER BY c.dateConsultation DESC, c.idConsultation DESC
  LIMIT 200
");
$stmt->execute([':from' => $from, ':to' => $to]);
$rows = $stmt->fetchAll();

// Logo local (obligatoire pour un PDF stable)
$logoPath = __DIR__ . '/logo_isst.png';
if (!file_exists($logoPath)) {
  http_response_code(500);
  echo "Erreur : logo introuvable. Ajoute le fichier 'logo_isst.png' dans le même dossier que rapport_general.php";
  exit;
}

$html = '
<html><head><meta charset="utf-8">
<style>
  @page { margin: 14mm 12mm 14mm 12mm; }
  body { font-family: sans-serif; font-size: 11pt; color: #111827; }
  .header { border-bottom: 1px solid #e5e7eb; padding-bottom: 8mm; margin-bottom: 6mm; }
  .tbl { width: 100%; border-collapse: collapse; }
  .kpis td { border: 1px solid #e5e7eb; padding: 8px; }
  .klabel { font-size: 9pt; color: #6b7280; text-transform: uppercase; font-weight: bold; }
  .kval { font-size: 14pt; font-weight: bold; margin-top: 3px; }
  h1 { text-align: center; font-size: 16pt; margin: 2mm 0 2mm 0; text-transform: uppercase; }
  .sub { text-align:center; color:#374151; margin:0 0 6mm 0; }
  .table th, .table td { border:1px solid #e5e7eb; padding:6px; vertical-align: top; }
  .table th { background:#f3f4f6; font-size: 9.5pt; }
  .small { font-size: 9.5pt; color:#374151; }
  .badge { padding:2px 8px; border-radius:999px; font-size:9pt; font-weight:bold; }
  .ok { background:#dcfce7; color:#166534; }
  .warn { background:#ffedd5; color:#9a3412; }
</style>
</head><body>

<div class="header">
  <table class="tbl">
    <tr>
      <td style="width:90px;">
        <img src="'.$logoPath.'" style="width:80px;height:80px;object-fit:contain;" />
      </td>
      <td>
        <div style="font-size:14pt;font-weight:bold;">Institut Supérieur des Sciences et Technologies La Sapience (ISST La Sapience)</div>
        <div class="small">
          Yaoundé – Ébang, 15 km du centre-ville sur la Nationale № 1, à 50m de l\'école publique d\'Ébang, Cameroun<br/>
          BP 5832 Yaoundé • Tél: +237 698 392 702 / 699 424 616 / 699 677 176 / 675 250 180 • infos@isst-edu.cm
        </div>
      </td>
      <td style="width:160px;text-align:right;">
        <div class="small"><b>Date:</b> '.date('d/m/Y').'</div>
        <div class="small"><b>Période:</b><br/>'.$from.' → '.$to.'</div>
      </td>
    </tr>
  </table>
</div>

<h1>Rapport de l’infirmerie</h1>
<div class="sub">Rapport général des consultations et indicateurs sur la période sélectionnée</div>

<table class="tbl kpis">
  <tr>
    <td><div class="klabel">Total étudiants</div><div class="kval">'.$totalEleves.'</div></td>
    <td><div class="klabel">Consultations (période)</div><div class="kval">'.$consultationsPeriode.'</div></td>
    <td><div class="klabel">Consultations aujourd\'hui</div><div class="kval">'.$consultationsToday.'</div></td>
    <td><div class="klabel">À traiter</div><div class="kval">'.$enAttente.'</div></td>
  </tr>
</table>

<h3>Top motifs (Top 5)</h3>';

if (empty($topMotifs)) {
  $html .= '<p class="small">Aucune donnée motif sur la période.</p>';
} else {
  $html .= '<table class="tbl table"><thead><tr><th>Motif</th><th style="width:90px;">Nombre</th></tr></thead><tbody>';
  foreach ($topMotifs as $m) {
    $html .= '<tr><td>'.htmlspecialchars($m['motifLabel'], ENT_QUOTES, "UTF-8").'</td><td style="text-align:center;">'.(int)$m['cnt'].'</td></tr>';
  }
  $html .= '</tbody></table>';
}

$html .= '<h3 style="margin-top:6mm;">Détails des consultations (limité 200)</h3>
<p class="small">NB : la date de consultation est enregistrée sans heure dans la base.</p>';

if (empty($rows)) {
  $html .= '<p class="small">Aucune consultation trouvée sur la période.</p>';
} else {
  $html .= '
  <table class="tbl table">
    <thead>
      <tr>
        <th style="width:75px;">Date</th>
        <th style="width:95px;">Matricule</th>
        <th>Étudiant</th>
        <th style="width:60px;">Classe</th>
        <th>Motif</th>
        <th>Diagnostic</th>
        <th>Traitement</th>
        <th style="width:90px;">Infirmier(e)</th>
        <th style="width:60px;">État</th>
      </tr>
    </thead><tbody>';

  foreach ($rows as $r) {
    $treated = !empty($r['traitement']);
    $etat = $treated ? '<span class="badge ok">Traité</span>' : '<span class="badge warn">À voir</span>';

    $html .= '<tr>
      <td style="text-align:center;">'.date('d/m/Y', strtotime($r['dateConsultation'])).'</td>
      <td>'.htmlspecialchars($r['matricule'], ENT_QUOTES, "UTF-8").'</td>
      <td>'.htmlspecialchars($r['prenom'].' '.$r['nom'], ENT_QUOTES, "UTF-8").'</td>
      <td style="text-align:center;">'.htmlspecialchars($r['classe'], ENT_QUOTES, "UTF-8").'</td>
      <td>'.htmlspecialchars($r['motif'], ENT_QUOTES, "UTF-8").'</td>
      <td>'.htmlspecialchars($r['diagnostic'], ENT_QUOTES, "UTF-8").'</td>
      <td>'.htmlspecialchars($r['traitement'], ENT_QUOTES, "UTF-8").'</td>
      <td>'.htmlspecialchars($r['infirmierePrenom'].' '.$r['infirmiereNom'], ENT_QUOTES, "UTF-8").'</td>
      <td style="text-align:center;">'.$etat.'</td>
    </tr>';
  }

  $html .= '</tbody></table>';
}

$html .= '
<br/><br/>
<table class="tbl">
  <tr>
    <td style="width:48%; border:1px dashed #cbd5e1; padding:18mm 8mm;">
      <b>L’Infirmier(ère)</b><br/><span class="small">(Nom et signature)</span>
    </td>
    <td style="width:4%;"></td>
    <td style="width:48%; border:1px dashed #cbd5e1; padding:18mm 8mm;">
      <b>Visa Administration</b><br/><span class="small">(Cachet et signature)</span>
    </td>
  </tr>
</table>

</body></html>';

$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4']);
$mpdf->SetTitle("Rapport Infirmerie ISST - {$from} au {$to}");
$mpdf->WriteHTML($html);

$filename = "rapport_infirmerie_ISST_{$from}_{$to}.pdf";
$mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
