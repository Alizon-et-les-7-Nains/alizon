select exists (
    select *
    from _vendeur 
    where pseudo = :pseudo and mdp = :mdp
);