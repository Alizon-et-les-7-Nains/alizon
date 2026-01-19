select *
from _produit
where stock <= seuilAlerte 
    and idVendeur = :idVendeur
    and stock <> 0
order by stock / seuilAlerte asc;