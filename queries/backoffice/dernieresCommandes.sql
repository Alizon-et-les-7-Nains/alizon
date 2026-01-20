select idProduit, dateCommande, etatLivraison, quantite, nom, prix, idVendeur, idCommande, idPanier
from _contient natural join _produit natural join _commande
where idVendeur = :idVendeur
order by dateCommande desc
limit 6;