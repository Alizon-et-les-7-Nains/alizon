select *
from _produit
where stock >= seuilAlerte and stock <> 0 and idVendeur = :idVendeur
order by stock / seuilAlerte asc;