<?php 
// Dependencias

/*libreria Nominatim*/
use maxh\Nominatim\Nominatim;
require_once 'vendor/autoload.php';

/*libreria Leaflet*/
require_once 'libs/LeafletMaphp-main/LeafletMaphp.php';

// Crear objeto de la clase Nomatim
$url = "http://nominatim.openstreetmap.org/";
$nominatim = new Nominatim($url);

// Consulta inversa a Nominatim
$reverse = $nominatim->newReverse()
    ->latlon((float) $_GET['lat'],(float) $_GET['lon']);
$result = $nominatim->find($reverse);

// Valores sacados de la consulta de Nomatim referenciados a Variables
$nombre = !empty($result['display_name']) ? $result['display_name'] : '';
$calle = !empty($result['address']['road']) ? $result['address']['road'] : '';
$numero = !empty($result['address']['house_number']) ? $result['address']['house_number'] : '';
$cp = !empty($result['address']['postcode']) ? $result['address']['postcode'] : '';

// Creando objeto de la clase Leaflet
$map = new LeafletMaphp();

echo "
<!DOCTYPE html>\n
<html>\n
<head>\n
";

/*Mostrar esto dentro de la cabecera*/
echo $map->showHeadTags();
echo "
    <title>{$result['display_name']}</title>\n
</head>\n
<body>
";

// Añadir marcador en el mapa
$map->addMarker((float) $_GET['lat'],(float) $_GET['lon']);

// Añadir popUp en el mapa
$map->addPopUp(LeafletMaphp::MARKER,0, $_GET['lat'].",".$_GET['lon']);

// Añadir perímetro del lugar en el mapa siempre que se pueda
if(isset($result['geojson']) && $result['geojson']['type'] == 'Polygon'){
    $map->addPolygon($result['geojson']['cordinates'][0]);
}elseif($result['osm_type'] == 'relation'){
    $geoJSON_url ="http://polygons.openstreetmap.fr/get_geojson.py?id={$place['osm_id']}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $geoJSON_url);
    $geoJSON = curl_exec($ch);
    curl_close($ch);
    $map->addGeoJSON($geoJSON);
}

echo "<h1>{$result['display_name']}</h1>";

// Muestreo del mapa
echo $map->show();

var_dump($result);

// Mostrando los datos de debajo del mapa
echo"
<ul>\n
    <li><b>Nombre:</b> {$nombre}</li>\n
    <li><b>Calle:</b> {$calle}</li>\n
    <li><b>Número:</b> {$numero}</li>\n
    <li><b>Código postal:</b> {$result['address']['postcode']}</li>\n
</ul>\n
";
echo '</body></html>';

?>