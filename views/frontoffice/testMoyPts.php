<?php
    require_once '../../controllers/auth.php';
    require_once '../../controllers/pdo.php';

    $sql_lat = "SELECT latitude FROM _adresseVendeur";
    $stmt_lat = $pdo->prepare($sql_lat);
    $stmt_lat->execute([]);
    $latitudes = $pdo->fetchAll(PDO::FETCH_ASSOC);

    $sql_lng = "SELECT longitude FROM _adresseVendeur";
    $stmt_lng = $pdo->prepare($stmt_lng);
    $stmt_lng->execute([]);
    $longitudes = $pdo->fetchAll(PDO::FETCH_ASSOC);

    $resLat = 0;
    $resLng = 0;
    $totalLat = 0;
    $totalLng = 0;


    $corner1Lat=$latitudes[0];
    $corner1Lng=$longitudes[0]; 
    
    $corner2Lat=$latitudes[0]; 
    $corner2Lng=$longitudes[0];

    foreach($latitudes as $lat){
        $resLat+=$lat;
        $totalLat++;

        if($corner1Lat < $lat){
            $corner1Lat = $lat;
        }

        if($corner2Lat > $lat){
            $corner2Lat = $lat;
        }
    }

    foreach($longitudes as $lng){
        $resLng+=$lng;
        $totalLng++;

        if($corner1Lng < $lng){
            $corner1Lng = $lng;
        }

        if($corner2Lng > $lng){
            $corner2Lng = $lng;
        }
    }

    $latMoy = $resLat/$totalLat;
    $lngMoy = $resLng/$totalLng;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>

<script>
    bounds = L.latLngBounds(corner1, corner2);
</script>