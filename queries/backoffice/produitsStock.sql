select *
from _produit
where stock >= seuilAlerte and idVendeur = :idVendeur
order by stock / seuilAlerte asc;