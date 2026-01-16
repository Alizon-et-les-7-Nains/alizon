create or replace trigger tg_delete_commande before delete on _commande for each row 
begin
    delete from _contient where idCommande = old.idCommande;
end;