select a.idProduit, a.idClient, dateAvis, titreAvis, contenuAvis, a.note, positifs, negatifs, 
       p.nom nomProduit, c.nom nomClient, p.idVendeur
from _avis a
join _produit p on a.idProduit = p.idProduit
join _client c on a.idClient = c.idClient
where p.idVendeur = :idVendeur
order by dateAvis desc
limit 4;
