select 
    idProduit, dateCommande, etatLivraison, quantiteProduit, nom, prix, idVendeur, idCommande, idPanier
from _produitAuPanier natural join _produit natural join _commande
order by dateCommande desc
limit 6;