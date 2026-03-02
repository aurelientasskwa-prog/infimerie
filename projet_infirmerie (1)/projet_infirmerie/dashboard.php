<?php
require_once __DIR__ . '/config.php';

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-t');

$totalEleves = 0;
$consultationsToday = 0;
$consultationsPeriode = 0;
$enAttente = 0;
$lowStockCount = 0;
$dernieresConsultations = [];

try {
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

    // Bas stock (seuil 10)
    $lowStockCount = (int)$pdo->query("
        SELECT COUNT(*)
        FROM medicament
        WHERE stockDisponible IS NOT NULL AND stockDisponible <= 10
    ")->fetchColumn();

    // 10 dernières consultations
    $sql = "
        SELECT
            c.idConsultation,
            c.dateConsultation,
            c.motif,
            c.diagnostic,
            c.traitement,
            e.matricule,
            e.nom,
            e.prenom,
            e.classe,
            i.nom AS infirmiereNom,
            i.prenom AS infirmierePrenom
        FROM consultation c
        JOIN etudiant e ON e.idEtudiant = c.idEtudiant
        JOIN infirmiere i ON i.idInfirmiere = c.idInfirmiere
        ORDER BY c.dateConsultation DESC, c.idConsultation DESC
        LIMIT 10
    ";
    $dernieresConsultations = $pdo->query($sql)->fetchAll();

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
</head>

<body class="h-full bg-slate-50 font-sans text-slate-800">
<div class="flex h-full">

    <!-- Sidebar -->
    <aside class="hidden md:flex md:flex-col md:w-64 bg-[#539EFF] text-white shadow-xl">
        <div class="p-6 flex items-center gap-3 border-b border-blue-300/40">
            <i class="fas fa-heartbeat text-3xl"></i>
            <span class="text-xl font-bold">INFIRMERIE</span>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="flex items-center px-4 py-3 bg-white/20 rounded-lg font-medium">
                <i class="fas fa-home mr-3"></i> Dashboard
            </a>

            <a href="listeConsultation.html" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-notes-medical mr-3"></i> Consultations
            </a>

            <a href="ajouterEtudiant.html" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-user-graduate mr-3"></i> Étudiant
            </a>

            <a href="#" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition" title="À implémenter">
                <i class="fas fa-pills mr-3"></i> Stock médicament
            </a>

            <a href="rapport_general.php" class="flex items-center px-4 py-3 hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-file-pdf mr-3"></i> Rapports
            </a>
        </nav>

        <div class="p-4 text-xs text-white/75 border-t border-white/15">
            © <?= date('Y') ?> ISST La Sapience • Infirmerie
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-slate-200 h-16 flex items-center justify-between px-6">
            <div>
                <div class="text-xs text-slate-500">Période</div>
                <div class="font-bold"><?= h($from) ?> → <?= h($to) ?></div>
            </div>

            <div class="flex items-center gap-3">
                <form method="GET" class="hidden md:flex items-end gap-2">
                    <div>
                        <label class="block text-xs text-slate-500">Du</label>
                        <input type="date" name="from" value="<?= h($from) ?>" class="h-9 px-3 rounded-lg border border-slate-200 text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500">Au</label>
                        <input type="date" name="to" value="<?= h($to) ?>" class="h-9 px-3 rounded-lg border border-slate-200 text-sm" />
                    </div>
                    <button class="h-9 px-4 rounded-lg bg-slate-900 text-white text-sm font-semibold">Filtrer</button>
                </form>

                <a
                    class="h-9 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold flex items-center gap-2"
                    href="rapport_general.php?from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>"
                    target="_blank"
                >
                    <i class="fa-solid fa-file-arrow-down"></i> Rapport PDF
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6">
            <?php if(isset($error_msg)): ?>
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg border border-red-200">
                    <i class="fas fa-bug mr-2"></i> <?= h($error_msg) ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-5 rounded-xl border">
                    <div class="text-xs uppercase text-slate-400 font-bold">Total étudiants</div>
                    <div class="text-3xl font-extrabold mt-2"><?= (int)$totalEleves ?></div>
                </div>
                <div class="bg-white p-5 rounded-xl border">
                    <div class="text-xs uppercase text-slate-400 font-bold">Consultations aujourd'hui</div>
                    <div class="text-3xl font-extrabold mt-2"><?= (int)$consultationsToday ?></div>
                </div>
                <div class="bg-white p-5 rounded-xl border">
                    <div class="text-xs uppercase text-slate-400 font-bold">Consultations période</div>
                    <div class="text-3xl font-extrabold mt-2"><?= (int)$consultationsPeriode ?></div>
                </div>
                <div class="bg-white p-5 rounded-xl border">
                    <div class="text-xs uppercase text-slate-400 font-bold">À traiter</div>
                    <div class="text-3xl font-extrabold mt-2"><?= (int)$enAttente ?></div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border mb-6 flex items-center justify-between">
                <div>
                    <div class="font-bold">Alerte stock médicaments</div>
                    <div class="text-sm text-slate-500">Stock ≤ 10</div>
                </div>
                <div class="text-2xl font-extrabold text-rose-600"><?= (int)$lowStockCount ?></div>
            </div>

            <div class="bg-white rounded-xl border overflow-hidden">
                <div class="px-6 py-4 border-b">
                    <h3 class="font-bold text-slate-700">Derniers passages à l'infirmerie</h3>
                </div>

                <?php if(empty($dernieresConsultations)): ?>
                    <div class="p-8 text-center text-slate-400">Aucune donnée trouvée.</div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Étudiant</th>
                            <th class="px-6 py-3">Classe</th>
                            <th class="px-6 py-3">Motif</th>
                            <th class="px-6 py-3">État</th>
                            <th class="px-6 py-3">Infirmier(e)</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <?php foreach ($dernieresConsultations as $c): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3 text-slate-500"><?= date('d/m/Y', strtotime($c['dateConsultation'])) ?></td>
                                <td class="px-6 py-3 font-medium">
                                    <?= h($c['prenom'].' '.$c['nom']) ?>
                                    <div class="text-xs text-slate-400"><?= h($c['matricule']) ?></div>
                                </td>
                                <td class="px-6 py-3 text-slate-500"><?= h($c['classe']) ?></td>
                                <td class="px-6 py-3"><?= h($c['motif']) ?></td>
                                <td class="px-6 py-3">
                                    <?php if(empty($c['traitement'])): ?>
                                        <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold">À voir</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">Traité</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-3 text-slate-500"><?= h($c['infirmierePrenom'].' '.$c['infirmiereNom']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>

        </main>
    </div>
</div>
</body>
</html>
