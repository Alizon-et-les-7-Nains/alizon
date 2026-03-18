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
    $placeholders = implode(',', array_fill(0, count($produits), '?'));

    $stmt = $pdo->prepare("
        SELECT nom, prix, URL, note FROM _produit 
        INNER JOIN _imageDeProduit ON _produit.idProduit = _imageDeProduit.idProduit 
        WHERE _produit.idProduit IN ($placeholders)
    ");
    $stmt->execute($produits);
    $produitsCatalogue = $stmt->fetchAll(PDO::FETCH_ASSOC);

    function getImageJpeg(string $cheminOriginal): ?string {
        if (!file_exists($cheminOriginal)) return null;
        $tempPath = sys_get_temp_dir() . '/' . uniqid('fpdf_', true) . '.jpg';
        $cmd = "/usr/bin/convert " . escapeshellarg($cheminOriginal) . " jpg:" . escapeshellarg($tempPath) . " 2>&1";
        exec($cmd, $output, $returnCode);
        if ($returnCode !== 0 || !file_exists($tempPath)) return null;
        return $tempPath;
    }

    function etoiles(float $note, int $max = 5): string {
        $plein = round($note);
        return str_repeat('★', $plein) . str_repeat('☆', $max - $plein);
    }

    // ── Classe PDF personnalisée ──────────────────────────────────────────────
    class CataloguePDF extends FPDF {

        // Couleurs
        const BLEU_FONCE  = [26,  54,  93];
        const BLEU_CLAIR  = [41,  98, 165];
        const GRIS_FOND   = [245, 247, 250];
        const GRIS_TEXTE  = [90,  90,  90];
        const OR          = [212, 160,  23];
        const BLANC       = [255, 255, 255];

        function Header() {
            // Bandeau bleu foncé
            $this->SetFillColor(...self::BLEU_FONCE);
            $this->Rect(0, 0, 210, 28, 'F');

            // Titre
            $this->SetFont('Arial', 'B', 22);
            $this->SetTextColor(...self::BLANC);
            $this->SetY(7);
            $this->Cell(0, 14, 'Catalogue Produits', 0, 0, 'C');

            // Filet doré sous le bandeau
            $this->SetDrawColor(...self::OR);
            $this->SetLineWidth(0.8);
            $this->Line(0, 28, 210, 28);

            $this->Ln(14);
        }

        function Footer() {
            $this->SetY(-14);
            $this->SetDrawColor(...self::BLEU_CLAIR);
            $this->SetLineWidth(0.4);
            $this->Line(10, $this->GetY(), 200, $this->GetY());
            $this->Ln(2);
            $this->SetFont('Arial', 'I', 8);
            $this->SetTextColor(...self::GRIS_TEXTE);
            $this->Cell(0, 6, 'Page ' . $this->PageNo(), 0, 0, 'C');
        }

        function carteProduit(array $produit, ?string $imagePath): void {
            $margeG  = 10;
            $largeur = 190;
            $hauteur = 52;

            // Vérifier si on dépasse la page
            if ($this->GetY() + $hauteur > 270) {
                $this->AddPage();
            }

            $yDebut = $this->GetY();

            // Fond de la carte
            $this->SetFillColor(...self::GRIS_FOND);
            $this->SetDrawColor(220, 225, 235);
            $this->SetLineWidth(0.3);
            $this->RoundedRect($margeG, $yDebut, $largeur, $hauteur, 3, 'FD');

            // ── Image (colonne gauche) ───────────────────────────────────────
            if ($imagePath !== null) {
                $this->Image($imagePath, $margeG + 3, $yDebut + 4, 42, 44, 'JPG');
            } else {
                // Placeholder gris si pas d'image
                $this->SetFillColor(200, 205, 215);
                $this->Rect($margeG + 3, $yDebut + 4, 42, 44, 'F');
                $this->SetFont('Arial', '', 8);
                $this->SetTextColor(...self::GRIS_TEXTE);
                $this->SetXY($margeG + 3, $yDebut + 22);
                $this->Cell(42, 8, 'Image indisponible', 0, 0, 'C');
            }

            // ── Texte (colonne droite) ───────────────────────────────────────
            $xTexte = $margeG + 50;
            $lTexte = $largeur - 50 - 5;

            // Nom du produit
            $this->SetFont('Arial', 'B', 13);
            $this->SetTextColor(...self::BLEU_FONCE);
            $this->SetXY($xTexte, $yDebut + 6);
            $this->Cell($lTexte, 8, utf8_decode($produit['nom']), 0, 2, 'L');

            // Séparateur fin
            $this->SetDrawColor(...self::BLEU_CLAIR);
            $this->SetLineWidth(0.3);
            $this->Line($xTexte, $this->GetY() + 1, $xTexte + $lTexte, $this->GetY() + 1);
            $this->Ln(4);

            // Note en étoiles
            $note      = round((float)$produit['note'], 1);
            $etoiles   = etoiles((float)$produit['note']);
            $this->SetFont('Arial', '', 12);
            $this->SetTextColor(...self::OR);
            $this->SetX($xTexte);
            $this->Cell($lTexte * 0.5, 7, $etoiles, 0, 0, 'L');

            $this->SetFont('Arial', '', 9);
            $this->SetTextColor(...self::GRIS_TEXTE);
            $this->Cell($lTexte * 0.5, 7, "(" . number_format($note, 1) . " / 5)", 0, 1, 'L');

            // Prix — badge coloré
            $this->Ln(3);
            $xPrix = $xTexte;
            $yPrix = $this->GetY();
            $this->SetFillColor(...self::BLEU_CLAIR);
            $this->SetTextColor(...self::BLANC);
            $this->SetFont('Arial', 'B', 13);
            $this->SetXY($xPrix, $yPrix);
            $this->Cell(40, 9, number_format((float)$produit['prix'], 2, ',', ' ') . ' EUR', 0, 0, 'C', true);

            // Revenir sous la carte
            $this->SetY($yDebut + $hauteur + 5);
        }

        // Rectangle à coins arrondis (FPDF n'en a pas nativement)
        function RoundedRect(float $x, float $y, float $w, float $h, float $r, string $style = ''): void {
            $k  = $this->k;
            $hp = $this->h;
            if ($style === 'F') $op = 'f';
            elseif ($style === 'FD' || $style === 'DF') $op = 'B';
            else $op = 'S';

            $MyArc = 4 / 3 * (sqrt(2) - 1);
            $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
            $xc = $x + $w - $r; $yc = $y + $r;
            $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
            $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
            $xc = $x + $w - $r; $yc = $y + $h - $r;
            $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
            $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
            $xc = $x + $r; $yc = $y + $h - $r;
            $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
            $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
            $xc = $x + $r; $yc = $y + $r;
            $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - $yc) * $k));
            $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
            $this->_out($op);
        }

        function _Arc(float $x1, float $y1, float $x2, float $y2, float $x3, float $y3): void {
            $h = $this->h;
            $this->_out(sprintf(
                '%.2F %.2F %.2F %.2F %.2F %.2F c',
                $x1 * $this->k, ($h - $y1) * $this->k,
                $x2 * $this->k, ($h - $y2) * $this->k,
                $x3 * $this->k, ($h - $y3) * $this->k
            ));
        }
    }

    // ── Génération ───────────────────────────────────────────────────────────
    $pdf = new CataloguePDF();
    $pdf->SetMargins(10, 35, 10);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();

    foreach ($produitsCatalogue as $produit) {
        $imagePath = '/var/www/html' . $produit['URL'];
        $imageTemp = getImageJpeg($imagePath);

        $pdf->carteProduit($produit, $imageTemp);

        if ($imageTemp !== null) unlink($imageTemp);
    }

    $pdf->Output('I', 'catalogue.pdf');
?>