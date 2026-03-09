<!-- À la fin du fichier, remplacer la section scripts existante par : -->

<?php 
// Récupération du mot de passe pour le JavaScript
$stmt = $pdo->prepare("SELECT mdp FROM _vendeur WHERE codeVendeur = :code_vendeur");
$stmt->execute([':code_vendeur' => $code_vendeur]);
$tabMdp = $stmt->fetch(PDO::FETCH_ASSOC);
$mdp = $tabMdp['mdp'] ?? '';
?>

<script>
    const adresseInput = document.getElementById('adresse');

    async function geocodeAdresse(adresse) {
        const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(adresse)}&format=json`;
        const rep = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
        const data = await rep.json();
        
        if (data.length > 0) {
            const { lat, lon } = data[0];
            latInput.value = lat;
            lngInput.value = lon;
            return { lat, lng: lon };
        } else {
            throw new Error("Adresse introuvable");
        }
    }

    geocodeAdresse(adresseInput.value);
</script>
<script src="../../controllers/Chiffrement.js"></script>
<script>
// Variables globales pour le JavaScript
const codeVendeur = <?= $code_vendeur ?>;
const mdpCrypte = <?php echo json_encode($mdp); ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
<!-- Utilisation du fichier commun A2F pour backoffice -->
<script src="../partials/a2f-backoffice.js"></script>
<script src="../scripts/backoffice/compteVendeur.js"></script>
<script src="../../public/amd-shim.js"></script>
<script src="../../public/script.js"></script>