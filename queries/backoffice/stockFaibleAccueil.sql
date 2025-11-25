select *
from _produit
where idVendeur = :idVendeur
order by stock asc
limit 6;