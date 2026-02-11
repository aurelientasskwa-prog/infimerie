<?php

require_once 'db.php';

// Initialisation
$totalEleves = 0;
$consultationsJour = 0;
$enAttente = 0;
$dernieresConsultations = [];

try {
    // 1. Compteur Total Élèves (Table 'etudiant')
    $totalEleves = $pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();

    // 2. Consultations du jour (Table 'consultation', colonne 'dateConsultation')
  
    $consultationsJour = $pdo->query("SELECT COUNT(*) FROM consultation WHERE DATE(dateConsultation) = CURDATE()")->fetchColumn();

    // 3. En attente (Si la colonne 'statut' n'existe pas, on met 0 par défaut pour éviter l'
    $enAttente = $pdo->query("SELECT COUNT(*) FROM consultation WHERE traitement IS NULL OR traitement = ''")->fetchColumn();

    // 4. Liste des dernières consultations
    // Jointure entre 'consultation' et 'etudiant' via 'idEtudiant'
    $sql = "SELECT c.motif, c.dateConsultation, c.traitement, 
                   e.nom, e.prenom, e.classe 
            FROM consultation c 
            JOIN etudiant e ON c.idEtudiant = e.idEtudiant 
            ORDER BY c.dateConsultation DESC LIMIT 5";
    $stmt = $pdo->query($sql);
    $dernieresConsultations = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Erreur SQL : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Infirmerie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
         :root { --bleu-full: #539EFF; --bleu-moyen: #A9CEFF; --bleu-clair: #DCEBFF; --noir: #1F2937; }
         .stat-card:hover { transform: translateY(-5px); transition: 0.3s; }
    </style>
</head>

<body class="h-full bg-slate-50 font-sans text-slate-800">

    <div class="flex h-full">
        <!-- Sidebar -->
        <aside class="hidden md:flex md:flex-col md:w-64 bg-[var(--bleu-full)] text-white shadow-xl">
            <div class="p-6 flex items-center gap-3 border-b border-blue-400/30">
                <i class="fas fa-heartbeat text-3xl"></i>
                <span class="text-xl font-bold">INFIRMERIE</span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="#" class="flex items-center px-4 py-3 bg-white/20 rounded-lg font-medium"><i class="fas fa-home mr-3"></i> Dashboard</a>
                <a href="#" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition"><i class="fas fa-user-injured mr-3"></i> Élèves</a>
                <a href="#" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition"><i class="fas fa-notes-medical mr-3"></i> Consultations</a>
            </nav>
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-8">
                <h1 class="text-xl font-bold text-slate-700">Vue d'ensemble</h1>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium">Bienvenue, Infirmière</span>
                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold">I</div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8">
                
                <?php if(isset($error_msg)): ?>
                    <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg border border-red-200">
                        <i class="fas fa-bug mr-2"></i> <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="stat-card bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase">Total Étudiants</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2"><?= $totalEleves ?></h3>
                            </div>
                            <div class="p-3 bg-blue-50 text-blue-500 rounded-lg"><i class="fas fa-users text-xl"></i></div>
                        </div>
                    </div>

                    <div class="stat-card bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase">Consultations Jour</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2"><?= $consultationsJour ?></h3>
                            </div>
                            <div class="p-3 bg-green-50 text-green-500 rounded-lg"><i class="fas fa-calendar-day text-xl"></i></div>
                        </div>
                    </div>

                    <div class="stat-card bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-xs font-bold text-slate-400 uppercase">À Traiter</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2"><?= $enAttente ?></h3>
                            </div>
                            <div class="p-3 bg-orange-50 text-orange-500 rounded-lg"><i class="fas fa-user-clock text-xl"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Tableau Dernières Consultations -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-700">Derniers passages à l'infirmerie</h3>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-3">Élève</th>
                                <th class="px-6 py-3">Classe</th>
                                <th class="px-6 py-3">Motif</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">État</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($dernieresConsultations as $c): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 font-medium"><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></td>
                                <td class="px-6 py-3 text-slate-500"><?= htmlspecialchars($c['classe']) ?></td>
                                <td class="px-6 py-3"><?= htmlspecialchars($c['motif']) ?></td>
                                <td class="px-6 py-3 text-slate-400"><?= date('d/m H:i', strtotime($c['dateConsultation'])) ?></td>
                                <td class="px-6 py-3">
                                    <?php if(empty($c['traitement'])): ?>
                                        <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold">À voir</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Traité</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if(empty($dernieresConsultations)): ?>
                        <div class="p-8 text-center text-slate-400">Aucune donnée trouvée dans la base.</div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>
</body>
</html>
