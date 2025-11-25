select exists (
    select *
    from _vendeur 
    where codeVendeur = :id and mdp = :pass
);