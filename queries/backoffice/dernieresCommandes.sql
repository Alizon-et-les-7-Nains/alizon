select idProduit, dateCommande, etatLivraison, quantiteProduit, nom, prix, idVendeur, idCommande, idPanier
from _produitAuPanier natural join _produit natural join _commande
where idVendeur = :idVendeur
order by dateCommande desc
limit 6;