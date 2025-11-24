select exists (
    select 1
    from _vendeur 
    where pseudo = :pseudo and mdp = :mdp
);