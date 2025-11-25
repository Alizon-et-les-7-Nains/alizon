select quantiteCommande, dateCommande, p.idPanier, pro.idProduit, c.idCommande, pro.idVendeur
from _commande c join _panier p on c.idPanier = p.idPanier join _produitAuPanier pro on p.idPanier = pro.idPanier
where pro.idProduit = :idProduit and pro.idVendeur = :idVendeur
order by dateCommande desc
limit 3;