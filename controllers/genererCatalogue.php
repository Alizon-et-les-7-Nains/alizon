<?php

    session_start();
    require_once "pdo.php";
    require_once '/var/www/html/lib/tfpdf/ttfontfile.php';
    require('/var/www/html/lib/tfpdf/tfpdf.php');

    define('FPDF_FONTPATH', '/var/www/html/lib/tfpdf/font/');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../views/backoffice/produits.php');
        exit;
    }

    $produits = json_decode($_POST['selectedIds'], true);

    $placeholders = implode(',', array_fill(0, count($produits), '?'));

    $stmt = $pdo->prepare("
        SELECT nom, prix, URL, note, idVendeur FROM _produit INNER JOIN _imageDeProduit ON _produit.idProduit = _imageDeProduit.idProduit 
        WHERE _produit.idProduit IN ($placeholders)
    ");

    $stmt->execute($produits);
    $produitsCatalogue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("SELECT raisonSocial FROM _vendeur WHERE codeVendeur = ?");
    $stmt->execute([$produitsCatalogue[0]['idVendeur']]);
    $raisonSociale = $stmt->fetch(PDO::FETCH_COLUMN);

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

    $pdf = new tFPDF();
    $pdf->AddPage();
    $pdf->SetAutoPageBreak(true, 10);

    $pdf->AddFont('DejaVuSans','','DejaVuSans.ttf', true);
    $pdf->SetFont('DejaVuSans','',16);
    $pdf->Cell(0,10,'Catalogue produits de ' . $raisonSociale ,0,1, 'C');

    $largeurColonne = 65; // largeur pour 3 colonnes
    $hauteurBloc = 60;    // hauteur d’un produit

    $xStart = 10;
    $yStart = $pdf->GetY();

    $compteur = 0;

    foreach($produitsCatalogue as $produit){

        $col = $compteur % 3;
        $x = $xStart + ($col * $largeurColonne);
        $y = $yStart;

        $pdf->SetXY($x, $y);

        $pdf->SetFont('DejaVuSans','',14);
        $pdf->MultiCell($largeurColonne, 10, $produit['nom'], 0, 'C');

        $currentY = $pdf->GetY();

        $imagePath = '/var/www/html' . $produit['URL'];
        $imageTemp = getImageJpeg($imagePath);

        if ($imageTemp !== null) {
            $pdf->Image($imageTemp, $x + 10, $currentY, 40, 0, 'JPG');

            if ($imageTemp !== $imagePath) unlink($imageTemp);

            $currentY += 30;
        }

        $pdf->SetXY($x, $currentY);
        $pdf->SetFont('DejaVuSans','',10);
        $pdf->MultiCell($largeurColonne, 30, "Prix : ".$produit['prix']." euros", 0, 'C');

        $compteur++;

        if ($compteur % 3 == 0) {
            $yStart += $hauteurBloc;
        }
    }
    $pdf->Output('I', 'catalogue.pdf');
?>
