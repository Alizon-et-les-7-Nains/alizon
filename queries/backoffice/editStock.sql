UPDATE _produit
SET stock = stock + :stock
WHERE idProduit = :idProduit