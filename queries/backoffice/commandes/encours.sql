select unique idCommande, dateCommande, dateExpedition, dateLivraison, etatLivraison, montantCommandeHt, montantCommandeTTC, nomTransporteur, p.idClient, pseudo
from _commande natural join _panier p natural join _contient natural join _produit join _client c on p.idClient = c.idClient
where etatLivraison <> 'Livrée' and idVendeur = ?;