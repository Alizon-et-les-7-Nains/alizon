<?php
// Le code commence par appeler dompdf, recupères des information nécéssaire à la facture
// Avant de stopper le buffer. Ensuite on fait une page html puis on récupère tout ce qui est
// Dans le buffer. On le met dans une variable puis on transforme le contenu de cette
// Variable en fichier pdf.

require_once __DIR__ . '/../dompdf/autoload.inc.php';
require_once './pdo.php';

$idCommande = $_GET['id'];

global $pdo;


$stmt = $pdo->prepare("
    SELECT
        c.idCommande, c.dateCommande, c.montantCommandeHt, c.montantCommandeTTC,
        p.nbArticles, p.prixHt, p.prixTotalTvaPanier,
        cl.prenom, cl.nom, cl.email,
        a.adresse, a.codePostal, a.ville
    FROM _commande c
    JOIN _panier p ON c.idPanier = p.idPanier
    JOIN _client cl ON p.idClient = cl.idClient
    JOIN _adresseClient a ON c.idAdresseFact = a.idAdresse
    WHERE c.idCommande = :commande
    ");

$stmt->execute([':commande' => $idCommande]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    throw new Exception("Commande introuvable");
}

$tva = $data['montantCommandeTTC'] - $data['montantCommandeHt'];

ob_start();
?>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { text-align: center; }
        .bloc { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        .totaux td { text-align: right; }
    </style>
</head>
<body>
<h1>FACTURE</h1>

<div class="bloc">
    <strong>Client</strong><br>
    <?= htmlspecialchars($data['prenom'] . ' ' . $data['nom']) ?><br>
    <?= htmlspecialchars($data['email']) ?><br>
    <?= htmlspecialchars($data['codePostal'] . ' ' . $data['ville']) ?>
</div>

<div class="bloc">
    Facture n° <?= 'FAC-' . date('Y') . '-' . str_pad($data['idCommande'], 6, '0', STR_PAD_LEFT) ?><br>
    Date : <?= date('d/m/Y', strtotime($data['dateCommande'])) ?><br>
</div>

<table>
    <tr>
        <th>Nombre d’articles</th>
        <th>Total HT</th>
        <th>TVA</th>
        <th>Total TTC</th>
    </tr>
    <tr>
        <td><?= $data['nbArticles'] ?></td>
        <td><?= number_format($data['montantCommandeHt'], 2, ',', ' ') ?> €</td>
        <td><?= number_format($tva, 2, ',', ' ') ?> €</td>
        <td><?= number_format($data['montantCommandeTTC'], 2, ',', ' ') ?> €</td>
    </tr>
</table>

</body>
</html>
<?php

$html = ob_get_clean();

$dompdf = new \Dompdf\Dompdf(); 
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();
$path = __DIR__ . '/../factures';

if(!is_dir($path)){
    mkdir($path, 0755, true);
}
file_put_contents($path . '/facture_' . $data['idCommande'] . '.pdf', $dompdf->output());

$dompdf->stream('facture_' . $data['idCommande'] . '.pdf', ['Attachment' => false]);
