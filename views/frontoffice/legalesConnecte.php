<?php require_once "../../controllers/prix.php" ?>
<?php require_once "../../controllers/pdo.php" ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../public/style.css">
    <link rel="icon" href="/public/images/logoBackoffice.svg">
    <title>Alizon - CGV & CGU</title>
</head>

<body class="cgucgv">

    <?php include "../../views/frontoffice/partials/headerConnecte.php"; ?>

    <main>
        <h1>Conditions générales de vente(CGV)</h1>

        <main>
            <section>
                <p>Les conditions générales de vente sont accessibles à tout moment pendant votre
                    navigation
                    au bas de chacune des pages de notre site, en cliquant sur le lien « Conditions Générales
                    de Vente ».</p>
                <h2>1 - Produits & Prix</h2>
                <p>
                    Produits vendus par COBREC. Prix TTC en euros. Stocks limités. Photos non
                    contractuelles.
                </p>

                <h2>2 - Commande – Procédure double clic</h2>
                <p></p>La commande est réalisée en plusieurs étapes :
                <ol>
                    <li>Sélection des produits et ajout au panier</li>
                    <li>Validation du panier</li>
                    <li>Identification ou création de compte</li>
                    <li>Saisie de l'adresse de livraison</li>
                    <li>Choix du mode de livraison et de paiement</li>
                    <li>Vérification du récapitulatif et validation définitive (second clic formant le contrat)</li>
                </ol>

                <h2>3 - Accusé de réception (Art. 1127-2)</h2>
                <ul>
                    <li>Email immédiat de réception de commande</li>
                    <li>mail sous 24h confirmant l'acceptation et les délais de livraison</li>
                    <li>Contrat archivé 10 ans, accessible dans l'espace client</li>
                </ul>

                <h2>4 - Paiement</h2>
                <p>
                    Carte VISA sécurisée
                </p>

                <h2>5 - Livraison</h2>
                <p>
                    <span>Zone</span> : France métropolitaine, Corse, Union Européenne
                    <span>Délais</span> : 3 à 5 jours ouvrés
                    <span>Frais de livraison</span> : proportionnel au volume du produit, détaché du
                    prix du produit
                </p>

                <h2>6 - Droit de rétractation</h2>
                <p>
                    Conformément au Code de la consommation, vous disposez d'un délai de 14 jours pour
                    exercer votre droit de rétractation sans avoir à justifier de motifs ni à payer de pénalités.
                    <span>Procédure, conditions et exceptions conformes au Code de la consommation.</span>
                </p>

                <h2>7 - Garanties légale</h2>
                <ul>
                    <li><span>Garantie de conformité</span> : 2 ans à compter de la délivrance du bien</li>
                    <li><span>Garantie des vices cachés</span> : 2 ans à compter de la découverte du vice</li>
                </ul>
                <p>
                    Contact SAV : sav@cobrec.fr — 02 96 00 00 00
                </p>

                <h2>8 - Service client</h2>
                <p>
                    <span>Service client</span> : sav@cobrec.fr <br>
                    Du lundi au vendredi, 9h-17h <br>
                    <span>Plateforme européenne de règlement des litiges :</span> <br>
                    https://ec.europa.eu/consumers/odr

                </p>
            </section>

            <h1>Mentions légales (LCEN 2004)</h1>
            <section>
                <h2>Éditeur du site</h2>
                <ul>
                    <li><span>Alizon et les Sept Nains</span></li>
                    <li>Responsable de publication : Équipe Alizon et les Sept Nains</li>
                    <li>Contact : contact@alizon.bzh — 02 96 00 00 00</li>
                </ul>

                <h2>Entreprise commanditaire</h2>
                <ul>
                    <li><span>COBREC — SAS</span></li>
                    <li>Siège social : 12 Rue des Entrepreneurs, 35000 Rennes</li>
                    <li>RCS Rennes : 512 987 654</li>
                    <li>SIRET : 51298765400027</li>
                    <li>Capital social : 150 000 €</li>
                    <li>TVA intracommunautaire : FR48512987654</li>
                </ul>

                <h2>Hébergeur</h2>
                <p>
                    <span>IUT de Lannion - Université de Rennes</span> <br>
                    7 Rue Edouard Branly, 22300 Lannion <br>
                    Téléphone : 09 72 10 10 10
                </p>

                <h2>Contact et assistance</h2>
                <ul>
                    <li>Par téléphone : 09 54 87 92 25 (lundi au dimanche, 12h-18h)</li>
                    <li>Par email : contact@alizon.bzh</li>
                    <li>En ligne : via votre Espace Client</li>
                    <li>Formulaire de contact : http://10.253.5.104/views/frontoffice/contact.php</li>
                </ul>
            </section>
            <h1>Données personnelles (RGPD 2018)</h1>
            <section>
                <h2>Données collectées</h2>
                <p>Nom, prénom, email, adresse, historique des commandes, moyen de paiement (via
                    prestataire sécurisé), cookies.</p>

                <h2>Finalités</h2>
                <p>Gestion des commandes COBREC, facturation, statistiques, amélioration des services.</p>


                <h2>Vos droits</h2>
                <p>Accès, rectification, suppression, portabilité, opposition.
                    Contact : dpo@cobrec.fr</p>

                <h2>Réclamation</h2>
                <p>Vous pouvez adresser une réclamation à la CNIL : www.cnil.fr</p>

                <h2>Durées de conservation</h2>
                <ul>
                    <li>Compte : 3 ans d'inactivité</li>
                    <li>Factures : 10 ans</li>
                    <li>Cookies : 13 mois</li>
                </ul>

                <h2>Sécurité</h2>
                <p>Paiements sécurisés, mot de passe chiffré.</p>
            </section>


            <h1>Propriété intellectuelle (CPI)</h1>
            <section>
                <p>Alizon est propriétaire et/ou dispose des autorisations nécessaires pour diffuser l'intégralité
                    du contenu du site ou rendu disponible à travers le site, notamment les textes, dessins,
                    graphiques, images, photos, marques et logos. Tous contenus appartiennent à COBREC ou
                    sont produits par Alizon et les Sept Nains dans le cadre du projet.
                    Ces contenus sont protégés par des droits de propriété intellectuelle. Il vous est par
                    conséquent interdit de copier, modifier, retranscrire, extraire, réutiliser et d'une manière
                    générale reproduire et diffuser les dits contenus sans l'autorisation expresse d'Alizon ou de
                    COBREC. <br>
                    <span>La violation des droits de propriété intellectuelle est constitutive du délit de
                        contrefaçon, pénalement sanctionné.</span>
                    <br>
                    En conséquence, sauf autorisation, vous vous engagez à ne pas :
                </p>

                <ul>
                    <li>Intégrer tout ou partie du contenu du site dans un site tiers, à des fins commerciales
                        ou non.</li>
                    <li>Extraire et/ou réutiliser tout ou partie des données accessibles via le site.</li>
                </ul>
            </section>

            <h1>Sécurité INFORMATIQUEinformatique (Art. 323 Code pénal)</h1>
            <section>
                <p>
                    <span>Interdictions</span> : intrusions, altération de données, perturbation du
                    service. <br>
                    <span>Infractions pénales applicables.</span> <br>
                    Si vous remarquez un contenu inapproprié ou illicite, nous vous invitons à nous le signaler
                    via le formulaire disponible en bas de chaque page de notre site ou à l'adresse :
                    http://10.253.5.104/views/frontoffice/contact.php
                </p>
            </section>


            <h1>Conditions générales d'utilisateurs (CGU)</h1>
            <section>
                <h2>Accès</h2>
                <p>Gratuit</p>

                <h2>Compte utilisateur</h2>
                <p>Les informations fournies doivent être exactes. Vous êtes responsable de la confidentialité
                    de vos identifiants.</p>

                <h2>Utilisation autorisée</h2>
                <p>Usage personnel uniquement.</p>

                <h2>Interdits :</h2>

                <p><span>Interdits :
                    </span></p>
                <ul>
                    <li>Extraction massive de données</li>
                    <li>Utilisation de bots</li>
                    <li>Actes illicites</li>
                </ul>

                <h2>Avis clients</h2>
                <p>Les avis sont modérés pour garantir respect et conformité.</p>

                <h2>Limitation de responsabilité</h2>
                <p>L'accès au site n'est pas garanti de manière continue. COBREC n'est responsable que des
                    dommages directs causés par un dysfonctionnement du site</p>

                <h2>Droit applicable</h2>
                <p>Droit français.</p>
            </section>


            <?php require_once '../backoffice/partials/retourEnHaut.php' ?>
        </main>

        <?php include "../../views/frontoffice/partials/footerConnecte.php"; ?>

</body>

</html>