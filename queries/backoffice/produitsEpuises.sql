select *
from _produit
where stock = 0 and idVendeur = :idVendeur
order by dateStockEpuise asc;