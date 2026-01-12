select unique idPanier, p.idProduit, quantiteProduit, p.nom, p.prix
from _commande natural join _produitAuPanier pp join _produit p on p.idProduit = pp.idProduit 
where idCommande = ? and p.idVendeur = ?
order by quantiteProduit desc;