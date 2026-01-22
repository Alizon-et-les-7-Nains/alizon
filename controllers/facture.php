<?php
// Le code commence par appeler dompdf, recupères des information nécéssaire à la facture
// Avant de stopper le buffer. Ensuite on fait une page html puis on récupère tout ce qui est
// Dans le buffer. 

require_once __DIR__ . '/../dompdf/autoload.inc.php';
require_once './pdo.php';

$idCommande = $_GET['id'];

global $pdo;

$stmt = $pdo->prepare("
    SELECT
        c.idCommande, c.dateCommande, c.montantCommandeHt, c.montantCommandeTTC, c.quantiteCommande,
        p.prixHt, p.prixTotalTvaPanier,
        cl.prenom, cl.nom, cl.email,
        a.adresse, a.codePostal, a.ville
    FROM _commande c
    JOIN _panier p ON c.idPanier = p.idPanier
    JOIN _client cl ON p.idClient = cl.idClient
    JOIN _adresseLivraison a ON c.idAdresseLivr = a.idAdresse
    WHERE c.idCommande = :commande
    ");

$stmt->execute([':commande' => $idCommande]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("    
    SELECT 
        p.nom, p.idProduit, p.prix, t.pourcentageTva, cnt.quantite
    FROM _commande c
    NATURAL JOIN _contient cnt 
    NATURAL JOIN _produit p
    NATURAL JOIN _typeTva t
    WHERE c.idCommande = :commande
");

$stmt->execute([':commande' => $idCommande]);
$prodVend = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt->execute([':commande' => $idCommande]);

if (!$data) {
    throw new Exception("Commande introuvable");
}

$tva = $data['montantCommandeTTC'] - $data['montantCommandeHt'];

ob_start();
?><html>
<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #000;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .header {
            width: 100%;
            margin-bottom: 30px;
        }

        .header td {
            vertical-align: top;
            width: 50%;
        }

        .bloc {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
        }

        th {
            background-color: #f0f0f0;
        }

        .right {
            text-align: right;
        }

        .totaux td {
            border: none;
            padding: 4px;
        }

        .mentions {
            margin-top: 40px;
            font-size: 10px;
        }
    </style>
</head>
<body>

<h1>FACTURE</h1>

<table class="header">
    <tr>
        <td>
            <strong>Vendeur</strong><br>
            Alizon<br>
            3 rue Edouard Branly<br>
            22300 Lannion<br>
            France<br>
            Email : contact@alizon.fr<br>
            SIRET : 123 456 789 00012<br>
            TVA intracom : FR12 123456789
        </td>
        <td class="right">
            <strong>Facture n°</strong> <?= 'FAC-' . date('Y') . '-' . str_pad($data['idCommande'], 6, '0', STR_PAD_LEFT) ?><br>
            <strong>Date :</strong> <?= date('d/m/Y', strtotime($data['dateCommande'])) ?><br>
        </td>
    </tr>
</table>

<div class="bloc">
    <strong>Facturé à :</strong><br>
    <?= htmlspecialchars($data['prenom'] . ' ' . $data['nom']) ?><br>
    <?= htmlspecialchars($data['email']) ?><br>
    <?= htmlspecialchars($data['adresse']) ?><br>
    <?= htmlspecialchars($data['codePostal'] . ' ' . $data['ville']) ?><br>
    France
</div>

<table>
    <tr>
        <th>Référence</th>
        <th>Désignation</th>
        <th class="right">Qté</th>
        <th class="right">Prix HT</th>
        <th class="right">TVA</th>
        <th class="right">Total HT</th>
    </tr>

    <?php foreach ($prodVend as $prod): 
        $totalLigneHT = $prod['prix'] * $prod['quantite'];
        $montantTVA = $totalLigneHT * ($prod['pourcentageTva'] / 100);
    ?>
    <tr>
        <td><?= htmlspecialchars($prod['idProduit']) ?></td>
        <td><?= htmlspecialchars($prod['nom']) ?></td>
        <td class="right"><?= $prod['quantite'] ?></td>
        <td class="right"><?= number_format($prod['prix'], 2, ',', ' ') ?> €</td>
        <td class="right"><?= $prod['pourcentageTva'] ?> %</td>
        <td class="right"><?= number_format($totalLigneHT, 2, ',', ' ') ?> €</td>
    </tr>
    <?php endforeach; ?>
</table>

<table class="totaux" style="width: 40%; float: right; margin-top: 20px;">
    <tr>
        <td class="right"><strong>Total HT :</strong></td>
        <td class="right"><?= number_format($data['montantCommandeHt'], 2, ',', ' ') ?> €</td>
    </tr>
    <tr>
        <td class="right"><strong>Total TVA :</strong></td>
        <td class="right"><?= number_format($tva, 2, ',', ' ') ?> €</td>
    </tr>
    <tr>
        <td class="right"><strong>Total TTC :</strong></td>
        <td class="right"><strong><?= number_format($data['montantCommandeTTC'], 2, ',', ' ') ?> €</strong></td>
    </tr>
</table>

<div style="clear: both;"></div>

<div class="mentions">
    TVA applicable selon l’article 256 du CGI.<br>
    Facture émise conformément à la législation française.<br>
    En cas de retard de paiement, une indemnité forfaitaire de 40 € pour frais de recouvrement sera exigible.
</div>

</body>
</html>

<?php
// On met le HTML dans une variable puis on transforme le contenu 
// De cette variable en fichier pdf.
$html = ob_get_clean();

$dompdf = new \Dompdf\Dompdf(); 
$dompdf->loadHtml($html);
$dompdf->setPaper('A4');
$dompdf->render();
$path = __DIR__ . '/../factures';

// Si le dossier n'éxiste pas, on force sa création
if(!is_dir($path)){
    mkdir($path, 0755, true);
}
file_put_contents($path . '/facture_' . $data['idCommande'] . '.pdf', $dompdf->output());

$dompdf->stream('facture_' . $data['idCommande'] . '.pdf', ['Attachment' => false]);
