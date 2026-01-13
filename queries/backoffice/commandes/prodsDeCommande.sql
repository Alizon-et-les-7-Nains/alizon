select unique idPanier, p.idProduit, quantite, p.nom, p.prix
from _commande natural join _contient pp join _produit p on p.idProduit = pp.idProduit 
where idCommande = ? and p.idVendeur = ?
order by quantite desc;