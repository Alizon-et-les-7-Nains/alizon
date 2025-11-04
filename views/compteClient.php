<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
</head>
<body>
    <?php include 'partials/header.php'; ?>
    <main>
        <div id="titreCompte">
            <img src="../public/images/profil.png" alt="photoProfil"></label>
            <input type="file" id="photoProfil" style="display:none">
            <h1>Mon Compte</h1>
        </div>

        <section>
            <article>
                <p>nlehebel</p>
                <p>Nathan</p>
                <p>Lehebel</p>
                <p>22/09/2006</p>
            </article>

            <article>
                <p>12 Rue des Fleurs</p>
                <p>Bâtiment B, Appartement 34</p>
            <div >
                <p>75015</p>
                <p>Paris</p>
            </div>
                <p>France</p>
            </article>

            <article>
                <p>06 73 98 01 38</p>
                <p>lehebel.nathan@gmail.com</p>
            </article> 
        </section>

        <div id ="buttonsCompte">
            <button class ="boutonModifierMdp">Modifier le mot de passe</button>
            <button class ="boutonModiferProfil">Modifier</button>
        </div>
        
    </main>
    <?php include 'partials/footer.php'; ?>
</body>
<link rel="stylesheet" href="../public/style/styleCompteClient.css">
<script>
    function popUpModifierMdp(){
        maFenetre = window.open("modifierMdp.html","popUp","width=900, height=450 screenX=350, screenY=150");
        maFenetre.focus();
   }

   let enModif = false;

   function modifierProfil(){;
    let bnModifier = document.getElementsByClassName("boutonModiferProfil");

        if (!enModif){
            let elems = document.querySelectorAll("section p");
            for (let i = 0; i < elems.length; i++){
                let texteActuel = elems[i].innerText;
                let input = document.createElement("input");
                input.type = "text";
                input.value = texteActuel;
                elems[i].parentNode.replaceChild(input, elems[i]);
            }
            bnModifProfil[0].innerHTML = "Enregistrer";
            bnModifProfil[0].style.backgroundColor = "#259525";
            bnModifProfil[0].style.color = "#FFFEFA";
            enModif = true;
        }

        else {
            let elems = document.querySelectorAll("section input");
            for (let i = 0; i < elems.length; i++){
                let texteActuel = elems[i].value;
                let p = document.createElement("p");
                p.innerText = texteActuel;
                elems[i].parentNode.replaceChild(p, elems[i]);
        } 
            bnModifProfil[0].innerText = "Modifer";
            bnModifProfil[0].style.backgroundColor = "#E4D9FF";
            bnModifProfil[0].style.color = "#273469";
            enModif = false;
        }

    }

    let bnModifMdp = document.getElementsByClassName("boutonModifierMdp");
    bnModifMdp[0].addEventListener("click", popUpModifierMdp);

    let bnModifProfil = document.getElementsByClassName("boutonModiferProfil");
    bnModifProfil[0].addEventListener("click", modifierProfil);
</script>
</html>