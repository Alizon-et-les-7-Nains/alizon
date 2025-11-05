<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
</head>
<body>
    <?php include 'frontoffice/partials/headerConnecte.php'; ?>
    <main class="mainCompteClient">
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
    <?php include 'frontoffice/partials/footerConnecte.php'; ?>
</body>
<script>
    function popUpModifierMdp(){
        maFenetre = window.open("modifierMdp.html","popUp","width=900, height=450, screenX=350, screenY=150");
        maFenetre.focus();
   }

   function verifierChamp() {
        const bouton = document.querySelector(".boutonModiferProfil");
        const champs = document.querySelectorAll("section input");
        let tousRemplis = true;

        for (let i = 0; i < champs.length; i++) {
            let valeur = champs[i].value.trim();
            if (i !== 5 && valeur === "") {
                tousRemplis = false;
                break;
            }

            if (i === 3) { 
                if (!/^([0][1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/.test(valeur)) {
                    tousRemplis = false;
                    break;
                }
            }

            if (i === 9) { 
                if (!/^0[67](\s[0-9]{2}){4}$/.test(valeur)) {
                    tousRemplis = false;
                    break;
                }
            }

            if (i === 10) {
                if (!/^[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z]{2,}$/.test(valeur)) {
                    tousRemplis = false;
                    break;
                }
            }            
        }
        bouton.disabled = !tousRemplis;
    }

    verifierChamp();
    document.querySelector("section").addEventListener("input", verifierChamp);

   let enModif = false;

   function modifierProfil(){
    let bnModifier = document.getElementsByClassName("boutonModiferProfil");

        if (!enModif){
            let elems = document.querySelectorAll("section p");
            for (let i = 0; i < elems.length; i++){
                if(i==9){
                    let texteActuel = elems[i].innerText;
                    let input = document.createElement("input");
                    input.type = "email";
                    input.value = texteActuel;
                    elems[i].parentNode.replaceChild(input, elems[i]);
                    
                }

                else if(i==10){
                    let texteActuel = elems[i].innerText;
                    let input = document.createElement("input");
                    input.type = "tel";
                    input.value = texteActuel;
                    elems[i].parentNode.replaceChild(input, elems[i]);
                }

                else{
                    let texteActuel = elems[i].innerText;
                    let input = document.createElement("input");
                    input.type = "text";
                    input.value = texteActuel;
                    elems[i].parentNode.replaceChild(input, elems[i]);
                }
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
<link rel="stylesheet" href="../../public/style.css">
</html>