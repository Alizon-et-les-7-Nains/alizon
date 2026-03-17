<?php
    session_start();
    require_once "pdo.php";
    require('../lib/fpdf/fpdf.php');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../views/backoffice/produits.php');
        exit;
    }

    if (!isset($_POST['produits'])) {
        echo "Aucun produit sélectionné";
        exit;
    }

    $produits = $_POST['produits'];

    //On fait ens sorte de faire (?, ?, ?) en fonction du nombre de produits sélectionnés ici 3 donc 3 points d'interrogation
    $placeholders = implode(',', array_fill(0, count($produits), '?'));

    $stmt = $pdo->prepare("
        SELECT nom, prix, URL, note FROM _produit INNER JOIN _imageDeProduit ON  _produit.idProduit = _imageDeProduit.idProduit 
        WHERE _produit.idProduit IN ($placeholders)
    ");

    $stmt->execute($produits);
    $produitsCatalogue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    print_r($produitsCatalogue);    

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);

    $pdf->Cell(0,10,'Catalogue produits',0,1);

    foreach($produitsCatalogue as $produit){

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,$produit['nom'],0,1);

        $pdf->Image( "/var/www/html/". $produit['URL'],10,$pdf->GetY(),40);

        $pdf->Ln(30);

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(0,10,"Prix : ".$produit['prix']." €",0,1);

        $pdf->Ln(10);
    }

?>