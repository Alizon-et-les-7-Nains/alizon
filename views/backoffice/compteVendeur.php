<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <title>Document</title>
</head>

<body class="monCompte">
    <?php require_once './partials/header.php' ?>

    <form method="POST" enctype="multipart/form-data" action="">
        <div id="titreCompte">
            <div class="photo-container">
                <?php 
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
                <div>
                    <p><?php echo htmlspecialchars($pseudo ?? ''); ?></p>
                </div>
                <div>
                    <p><?php echo htmlspecialchars($prenom ?? ''); ?></p>
                </div>
                <div>
                    <p><?php echo htmlspecialchars($nom ?? ''); ?></p>
                </div>
                <div>
                    <p><?php echo htmlspecialchars($dateNaissance ?? ''); ?></p>
                </div>
            </article>

            <article>
                <div>
                    <p><?php echo htmlspecialchars($adresse1 ?? ''); ?></p>
                </div>
                <div>
                    <p><?php echo htmlspecialchars(" "); ?></p>
                </div>
                <div class="double-champ">
                    <div>
                        <p><?php echo htmlspecialchars($codePostal ?? ''); ?></p>
                    </div>
                    <div>
                        <p><?php echo htmlspecialchars($ville ?? ''); ?></p>
                    </div>
                </div>
                <div>
                    <p><?php echo htmlspecialchars($pays ?? ''); ?></p>
                </div>
            </article>

            <article>
                <div>
                    <p><?php echo htmlspecialchars($noTelephone ?? ''); ?></p>
                </div>
                <div>
                    <p><?php echo htmlspecialchars($email ?? ''); ?></p>
                </div>
            </article>
        </section>

        <div id="buttonsCompte">
            <button type="button" onclick="popUpModifierMdp()" class="boutonModifierMdp">Modifier le mot de
                passe</button>
            <button class="boutonAnnuler" type="button" onclick="boutonAnnuler()">Annuler</button>
            <button type="button" class="boutonModiferProfil">Modifier</button>
        </div>
    </form>

    </main>

    <?php require_once './partials/footer.php' ?>
</body>

</html>