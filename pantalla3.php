<?php 
// Dependencias

/*libreria Nominatim*/
use maxh\Nominatim\Nominatim;
require_once 'vendor/autoload.php';

/*libreria Leaflet*/
require_once 'libs/LeafletMaphp-main/LeafletMaphp.php';



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
    <title>Pantalla 2</title>\n
</head>\n
<body>
";



echo '</body></html>';

?>