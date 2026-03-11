<?php
// Le code commence par appeler dompdf, recupères des information nécéssaire à la facture
// Avant de stopper le buffer. Ensuite on fait une page html puis on récupère tout ce qui est
// Dans le buffer. 

require_once __DIR__ . '/../dompdf/autoload.inc.php';
require_once './pdo.php';

global $pdo;

$stmt = $pdo->prepare("SELECT nom,poids,prix,note FROM _produit WHERE idVendeur = :vendeur");

$stmt->execute([':vendeur' => $_SESSION['id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    throw new Exception("Produit introuvable");
}

$tva = $data['montantCommandeTTC'] - $data['montantCommandeHt'];

ob_start();
?><html>
<head>
    <style>
        
    </style>
</head>
<body>

<h1>Catalogue des produits</h1>

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
