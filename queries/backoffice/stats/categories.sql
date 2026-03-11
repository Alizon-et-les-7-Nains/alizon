select distinct cat.nomCategorie
from _produit prd
    join _categorie cat
        on prd.idCategorie = cat.idCategorie
where
    idVendeur = ?;