select *
from _produit
where stock <> 0 and stock < seuilAlerte and idVendeur = :idVendeur
order by stock / seuilAlerte asc;