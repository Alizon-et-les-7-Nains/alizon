<?php

    session_start();
    require_once "pdo.php";
    require('../lib/fpdf/fpdf.php');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../views/backoffice/produits.php');
        exit;
    }

    $produits = json_decode($_POST['selectedIds'], true);

    $placeholders = implode(',', array_fill(0, count($produits), '?'));

    $stmt = $pdo->prepare("
        SELECT nom, prix, URL, note FROM _produit INNER JOIN _imageDeProduit ON _produit.idProduit = _imageDeProduit.idProduit 
        WHERE _produit.idProduit IN ($placeholders)
    ");

    $stmt->execute($produits);
    $produitsCatalogue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    function getImageJpeg(string $cheminOriginal): ?string {
        if (!file_exists($cheminOriginal)) return null;

        $tempPath = sys_get_temp_dir() . '/' . uniqid('fpdf_', true) . '.jpg';
        
        $cmd = "/usr/bin/convert " . escapeshellarg($cheminOriginal) . " jpg:" . escapeshellarg($tempPath) . " 2>&1";
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($tempPath)) {
            error_log("ImageMagick échec : " . implode("\n", $output));
            return null;
        }

        return $tempPath;
    }

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'Catalogue produits',0,1);

    foreach($produitsCatalogue as $produit){

        $imagePath = '/var/www/html' . $produit['URL'];
        $imageTemp = getImageJpeg($imagePath);

        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,$produit['nom'],0,1);

        if ($imageTemp !== null) {
            $pdf->Image($imageTemp, 10, $pdf->GetY(), 40, 0, 'JPG');
            // Suppression du fichier temp si conversion effectuée
            if ($imageTemp !== $imagePath) unlink($imageTemp);
            $pdf->Ln(30);
        } else {
            $pdf->Ln(5);
        }

        $pdf->SetFont('Arial','',10);
        $pdf->Cell(0,10,"Prix : ".$produit['prix']." €",0,1);
        $pdf->Ln(10);
    }

    $pdf->Output('I', 'catalogue.pdf');
?>
