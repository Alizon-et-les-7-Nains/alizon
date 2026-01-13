select *
from _produit
where stock <= 0 seuilAlerte and idVendeur = :idVendeur
order by stock / seuilAlerte asc;