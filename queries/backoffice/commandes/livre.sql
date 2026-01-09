select unique idCommande, dateCommande, etatLivraison, montantCommandeHt, montantCommandeTTC, nomTransporteur, idClient
from _commande natural join _panier natural join _produitaupanier natural join _produit
where etatLivraison = 'Livrée' and idVendeur = ?;