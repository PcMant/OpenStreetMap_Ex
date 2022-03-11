<?php session_start(); ?>

<!DOCTYPE html>
<html lang="es">
<head>

<?php

use maxh\Nominatim\Nominatim;

if (!empty($_SESSION['places']) && isset($_REQUEST['place'])) {
    require 'libs/LeafletMaphp-main/LeafletMaphp.php';
    $place = $_SESSION['places'][$_REQUEST['place']];

    try {
        /// Inicializamos Leaflet
        $map = new LeafletMaphp();
        //Incluimos en el head las etiquetas necesarias
        echo $map->showHeadTags()."\t<title>".$place['place_id'].
        "</title>\n</head>\n<body>\n";

        // Establecemos centro, zoom y tamaño del mapa
        $map->setCenter(
            $place['lat'],
            $place['lon'],
            $place["boundingbox"]
        );

        // Añadimos un marcador en la posición obtenida
        $map->addMarker($place['lat'], $place['lon']);
        ;

        // Añadimos un polígono que rodee el objeto si existe
        if ((isset($place['geojson'])) &&
        ($place['geojson']['type'] == 'Polygon')) {
            $map->addPolygon(
                $place['geojson']['coordinates'][0]
            );
        } elseif ($place['osm_type'] == 'relation') {
            $geoJSON_url =
            "http://polygons.openstreetmap.fr/get_geojson.py
            ?id={$place['osm_id']}";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $geoJSON_url);
            $geoJSON = curl_exec($ch);
            curl_close($ch);
            $map->addGeoJSON($geoJSON);
        }
            

        echo "{$place['display_name']}<br>\n";
        
        // Mostramos el mapa
        echo $map->show();
    } catch (Exception $e) {
        echo $e;
    }
} elseif (!empty($_REQUEST['keywords'])) {
    // Incluimos la clase Nominatim
    require 'vendor/autoload.php';
    
    try {
        // Creamos un objeto de la clase Nominatim
        $url = "http://nominatim.openstreetmap.org/";
        $nomin = new Nominatim($url);
    
        
        // Obtenemos las coordenadas del punto a mostrar
        $search = $nomin->newSearch()->query(
            $_REQUEST['keywords']
        )->polygon('geojson');
        $results = $nomin->find($search);
        echo "\t<title>Resultados de \"".$_REQUEST['keywords'].
    "\"</title>\n</head>\n<body>\n".count($results)
    . " resultados para \"".$_REQUEST['keywords'].
    "\":<br>\n<ol>\n";
        unset($_SESSION['places']);
    

        // Mostramos los lugares
        foreach ($results as $pl) {
            $_SESSION['places'][$pl['place_id']] = $pl;
            echo "\t<li><a href='ejemplo.php?place={$pl['place_id']}'>";
            if (isset($pl['icon'])) {
                echo "<img src='{$pl['icon']}'>";
            }
            echo "{$pl['display_name']}</a></li>\n";
        }
        echo "</ol>";
    } catch (Exception $e) {
        echo $e;
    }
} else {

    // Formulario para recoger la localización buscada
    echo "\t<title>Búsqueda de lugares</title>\n</head>\n<body>
    <form method='GET'>
    <h3>Localización a buscar </h3>
    <input type='search' name='keywords'>
    <input type='submit' value='Buscar'/>
    </form>";
}

?>

</body></html>

