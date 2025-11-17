<?php 
// $productId = intval($_GET['id']) ?? 0;

// if($productId == 0) {
//     die("Ce produit n'existe pas, ou n'est plus disponible");
// }
$images = [
    [
        'URL' => 'cidre.png',
        'title' => 'Premium Cidre'
    ],
    [
        'URL' => 'rillettes.png', 
        'title' => 'Artisanal Cidre'
    ],
    [
        'URL' => 'defaultImageProduit.png',
        'title' => 'Traditional Cidre'
    ]
];

$produit = [
    'nom_produit' => 'Cidre Artisanal Breton',
    'description' => 'Un cidre artisanal produit selon les méthodes traditionnelles bretonnes...',
    'prix' => 12.50,
    'prenom_vendeur' => 'Jean',
    'nom_vendeur' => 'Dupont',
    'stock' => 20 ];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../../public/style.css">
</head>
<?php // include "../../views/frontoffice/partials/headerConnecte.php" ?>
<body class="pageEcrireCommentaire">
    <section class="produit">
        <img src="../../public/images/<?php echo $images[0]['URL']?>" alt="">
        <h2><?php echo $images[0]['title']?></h2>
    </section>
    <hr>
    <section class="reviewArticle">
        <h1>Cet article vous a-t'il plus ?</h1>
        <h2>Laisser une note : </h2>
        <article class="etoiles">
            <img src="../../public/images/etoileVide.svg" data-index="1" class="star" alt="">
            <img src="../../public/images/etoileVide.svg" data-index="2" class="star" alt="">
            <img src="../../public/images/etoileVide.svg" data-index="3" class="star" alt="">
            <img src="../../public/images/etoileVide.svg" data-index="4" class="star" alt="">
            <img src="../../public/images/etoileVide.svg" data-index="5" class="star" alt="">
        </article>
        <input type="hidden" name="note" id="note">
        <h2>Ajouter des photos : </h2>

        <img src="../../public/images/ajouterPhoto.svg" alt="" id="ajouterPhoto">
        <h2>Ecrire un commentaire : </h2>
        <textarea name="sujet" id="sujet" placeholder="Sujet"></textarea>
        <textarea name="message" id="message" placeholder="Message"></textarea>
        <button class="bouton boutonBleu">Publier</button>
    </section>
</body>
<script>
    const noteInput = document.getElemeentById('note')
    const stars = document.querySelectorAll('.star');
    const emptyStar = "../../public/images/etoileVide.svg";
    const fullStar = "../../public/images/etoile.svg";
    stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            const rating = index + 1;

            stars.forEach((s, i) => {
                if (i < rating) {
                    s.src = fullStar;
                } else {
                    s.src = emptyStar
                }
            });

            noteInput.value = rating;
        });
    });
</script>
</html>