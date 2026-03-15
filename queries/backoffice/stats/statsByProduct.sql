select cmd.idCommande, dateCommande, prd.idProduit, prixProduitHt, quantite, nom, nomCategorie, idVendeur
from _commande cmd 
    join _contient ctn on cmd.idCommande = ctn.idCommande
    join _produit prd on ctn.idProduit = prd.idProduit
    join _categorie cat on prd.idCategorie = cat.idCategorie
where
    nom = ?
    and idVendeur = ?;