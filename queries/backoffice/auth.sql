select exists (
    select *
    from _vendeur 
    where codeVendeur = :id
);