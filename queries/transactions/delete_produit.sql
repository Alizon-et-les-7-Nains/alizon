create or replace trigger tg_delete_produit before delete on _produit for each row 
begin
    delete from _imagedeproduit where idProduit = old.idProduit;
    delete from _contient where idProduit = old.idProduit;
end;