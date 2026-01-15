select exists (
    select mdp
    from _vendeur 
    where pseudo = :pseudo
);