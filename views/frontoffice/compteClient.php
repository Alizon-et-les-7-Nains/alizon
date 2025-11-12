<?php
require_once '../../controllers/pdo.php' ;
    
session_start();

$id_client = 1; //$_SESSION['id_client'];
$idAdresse = 1; //$_SESSION['id_adresse'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //update la BDD avec les nouvelles infos du user
    $pseudo = $_POST['pseudo'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $codePostal = $_POST['codePostal'];
    $adresse1 = $_POST['adresse1'];
    $pays = $_POST['pays'];
    $ville = $_POST['ville'];
    $region = $_POST['region'] ?? '';

    $stmt = $pdo->query(
    "UPDATE _client 
    SET pseudo = '$pseudo', 
    nom = '$nom', 
    prenom = '$prenom', 
    email =  '$email', 
    noTelephone = '$telephone'
    WHERE idClient = '$id_client';");

    $stmt = $pdo->query(
    "UPDATE _adresse 
    SET adresse = '$adresse1',
    pays = '$pays',
    ville = '$ville', 
    codePostal = '$codePostal',
    region = '$region'
    WHERE idAdresse = '$idAdresse';");

    //verification et upload de la nouvelle photo de profil
    if (isset($_FILES['photoProfil']) && $_FILES['photoProfil']['tmp_name'] != '') {
        move_uploaded_file($_FILES['photoProfil']['tmp_name'], '../../public/images/photoDeProfil/photo_profil'.$id_client.'.png');
    }
}   
    //on recupère les infos du user pour les afficher
    $stmt = $pdo->query("SELECT * FROM _client WHERE idClient = '$id_client'");
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    $pseudo = $client['pseudo'];
    $prenom = $client['prenom'];
    $nom = $client['nom'];
    $dateNaissance = $client['dateNaissance'];
    $email = $client['email'];
    $noTelephone = $client['noTelephone'];

    //on recupère les infos d'adresse du user pour les afficher
    $stmt = $pdo->query("SELECT * FROM _adresse WHERE idAdresse = '$idAdresse'");
    $adresse = $stmt->fetch(PDO::FETCH_ASSOC);

    $pays = $adresse['pays'];
    $ville = $adresse['ville'];
    $codePostal = $adresse['codePostal'];
    $adresse1 = $adresse['adresse'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<body>
    <?php include 'partials/headerConnecte.php'; ?>

    <main class="mainCompteClient">
        <form method="POST" enctype="multipart/form-data" action="">
            <div id="titreCompte">
                <div class="photo-container">
                    <?php 
                        $id = 1;
                        $photoPath = '../../public/images/photoDeProfil/photo_profil'.$id.'.png';
                        if (file_exists($photoPath)) {
                            echo "<img src=".$photoPath." alt=photoProfil id=imageProfile>";
                        } else {
                            echo '<img src="../../public/images/profil.png" alt="photoProfil" id="imageProfile">';
                        }
                    ?>
                </div>
                <h1>Mon Compte</h1>
            </div>

            <section>
                <article>
                    <p><?php echo htmlspecialchars($pseudo ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($prenom ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($nom ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($dateNaissance ?? ''); ?></p>
                </article>

                <article>
                    <p><?php echo htmlspecialchars($adresse1 ?? ''); ?></p>
                    <p><?php echo htmlspecialchars(" "); ?></p>
                    <div>
                        <p><?php echo htmlspecialchars($codePostal ?? ''); ?></p>
                        <p><?php echo htmlspecialchars($ville ?? ''); ?></p>
                    </div>
                    <p><?php echo htmlspecialchars($pays ?? ''); ?></p>
                </article>

                <article>
                    <p><?php echo htmlspecialchars($noTelephone ?? ''); ?></p>
                    <p><?php echo htmlspecialchars($email ?? ''); ?></p>
                </article> 
            </section>

            <div id="buttonsCompte">
                <button type="button" onclick="popUpModifierMdp()" class="boutonModifierMdp">Modifier le mot de passe</button>
                <button class="boutonAnnuler" type="button" onclick="boutonAnnuler()">Annuler</button>
                <button type="button" class="boutonModiferProfil">Modifier</button>
            </div>
        </form>
    </main>
    
    <?php include 'partials/footerConnecte.php'; ?>

    <script>
        function popUpModifierMdp(){
            const overlay = document.createElement("div");
            overlay.className = "overlayPopUpCompteClient";
            overlay.innerHTML = `
                <main class="mainPopUpCompteClient">
                <h1>Modification de votre mot de passe</h1>
               
                <section>
                    <div class="formulaireMdp">
                        <form action="">
                            <input type="text" placeholder="Ancien mot de passe">
                            <input type="text" placeholder="Nouveau mot de passe">
                            <input type="text" placeholder="Confirmer le nouveau mot de passe">
                        
                            <article>
                                <div class="croix">
                                    <div></div>
                                    <div></div>
                                </div> 
                                <p>Longueur minimale de 12 charactères</p>
                            </article>

                            <article>
                                <div class="croix">
                                    <div></div>
                                    <div></div>
                                </div> 
                                <p>Au moins une minuscule / majuscule</p>
                            </article>

                            <article>
                                <div class="croix">
                                    <div></div>
                                    <div></div>
                                </div> 
                                <p>Au moins un chiffre</p>
                            </article>

                            <article>
                                <div class="croix">
                                    <div></div>
                                    <div></div>
                                </div>  
                                <p>Au moins un charactères spéciale</p>
                            </article>
                        </div>
                            <button type="button" onclick="fermerFenetre()">Valider</button>
                        </form>
                    </section>
                </main>`;
            document.body.appendChild(overlay);
        }

        function verifierChamp() {
            const bouton = document.querySelector(".boutonModiferProfil");
            const champs = document.querySelectorAll("section input");
            let tousRemplis = true;

            for (let i = 0; i < champs.length; i++) {
                let valeur = champs[i].value.trim();
                
                // Le champ adresse2 est optionnel
                if (i !== 5 && valeur === "") {
                    tousRemplis = false;
                    break;
                }

                // Validation spécifique pour le numéro de téléphone
                if (i === 9) { 
                    if (!/^0[67](\s[0-9]{2}){4}$/.test(valeur)) {
                        tousRemplis = false;
                        break;
                    }
                }

                // Validation spécifique pour l'email
                if (i === 10) {
                    if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/.test(valeur)) {
                        tousRemplis = false;
                        break;
                    }
                }            
            }

            bouton.disabled = !tousRemplis;
        }
        let enModif = false;

        // Création de l'input pour la photo de profil
        let ajoutPhoto = document.createElement("input");
        ajoutPhoto.type = "file";
        ajoutPhoto.id = "photoProfil";
        ajoutPhoto.name = "photoProfil";
        ajoutPhoto.accept = "image/*";
        ajoutPhoto.style.display = "none";
        ajoutPhoto.autocomplete = "off";
        
        let conteneur = document.getElementById("titreCompte");
        let imageProfile = document.getElementById("imageProfile");
        let bnModifier = document.getElementsByClassName("boutonModiferProfil");
        let bnModifMdp = document.getElementsByClassName("boutonModifierMdp");
        let bnAnnuler = document.getElementsByClassName("boutonAnnuler");

        function modifierProfil(event) {

            // Empêche le comportement par défaut du bouton
            event.preventDefault();

            if (!enModif) {
                // Remplacer les <p> par des <input> pour modification
                let elems = document.querySelectorAll("section p");
                const nomsChamps = [
                    "pseudo", "prenom", "nom", "dateNaissance",
                    "adresse1", "adresse2", "codePostal", "ville", "pays",
                    "telephone", "email"
                ];

                for (let i = 0; i < elems.length; i++) {
                    let texteActuel = elems[i].innerText;
                    let input = document.createElement("input");
                    input.value = texteActuel;
                    input.name = nomsChamps[i];
                    input.id = nomsChamps[i];
                    input.autocomplete = nomsChamps[i];

                    // Définir le type d'input approprié
                    if (i === 9) input.type = "tel";
                    else if (i === 10) input.type = "email";
                    else input.type = "text";

                    switch(i) {
                        case 0:
                            input.placeholder = "Entrez votre pseudo";
                            break;
                        case 1:
                            input.placeholder = "Entrez votre nom";
                            break;
                        case 2:
                            input.placeholder = "Entrez votre prénom";
                            break;
                        case 3:
                            input.placeholder = "Entrez votre date de naissance jj/mm/aaaa";
                            break;
                        case 4:
                            input.placeholder = "Entrez votre adresse";
                            break;
                        case 6:
                            input.placeholder = "Entrez votre code postal";
                            break;
                        case 7:
                            input.placeholder = "Entrez votre ville";
                            break;
                        case 8:
                            input.placeholder = "Entrez votre pays";
                            break;
                        case 9:
                            input.placeholder = "Entrez votre numéro de téléphone";
                            break;
                        case 10:
                            input.placeholder = "Entrez votre email";
                            break;
                    }

                    elems[i].parentNode.replaceChild(input, elems[i]);
                }

                // Modifier le bouton "Modifier" en "Enregistrer"
                bnModifier[0].innerHTML = "Enregistrer";
                bnModifier[0].style.backgroundColor = "#64a377";
                bnModifier[0].style.color = "#FFFEFA";
                conteneur.appendChild(ajoutPhoto);
                
                imageProfile.style.cursor = "pointer";
                imageProfile.onclick = () => ajoutPhoto.click();
                
                enModif = true;

                bnAnnuler[0].style.display = "block";
                bnAnnuler[0].style.color = "white";

                document.querySelector("section").addEventListener("input", verifierChamp);
                verifierChamp();

            } 
            
            else {
                // Soumettre le formulaire pour enregistrer les modifications
                document.querySelector("form").submit();
            }
        }

        bnModifier[0].addEventListener("click", modifierProfil);

        function fermerFenetre(){
            window.close();
        }

        function boutonAnnuler() {

            let inputs = document.querySelectorAll("section input");

            for (let i = 0; i < inputs.length; i++) {
                let p = document.createElement("p");
                p.innerText = inputs[i].value;
                inputs[i].parentNode.replaceChild(p, inputs[i]);
            }

            if (document.getElementById("photoProfil")) {
                document.getElementById("photoProfil").remove();
            }

            enModif = false;

            bnModifier[0].innerHTML = "Modifier";
            bnModifier[0].style.backgroundColor = "#e4d9ff";
            bnModifier[0].style.color = "#273469";

            bnAnnuler[0].style.display = "none";

            imageProfile.style.cursor = "default";
            imageProfile.onclick = null;
        }
    </script>
</body>
</html>