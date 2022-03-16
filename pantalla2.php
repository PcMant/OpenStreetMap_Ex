<?php 
require_once 'libs/LeafletMaphp-main/LeafletMaphp.php';

// Parametros obtenidos por GET guardado en variables
$codRuta = !empty($_GET['ruta']) ? $_GET['ruta'] : '';
$restaurantesG = !empty($_GET['restaurantes']) ? 'ok' : '';

// Obtener el nombre de la ruta en base de su código
$host_db = 'localhost';
$port_db = 3306;
$database = 'turismo';
$user_db = 'root';
$pass_db = '';

try {
    // conexión base de datos
    $pdo = new PDO("mysql:host={$host_db};port={$port_db};dbname={$database};charset=utf8", $user_db, $pass_db);
    

    //Consulta
    $sql = 'SELECT * FROM `rutas` where `cod`=:cod';
    $sentencia = $pdo->prepare($sql);
    $sentencia->bindParam(':cod', $codRuta);
    
    $sentencia->execute();
    $resultadosR = $sentencia->fetchAll();

    
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}

//var_dump($resultadosR);

$pdo = null;

// Obtener lugares sacados de la base de datos
try {
    // conexión base de datos
    $pdo = new PDO("mysql:host={$host_db};port={$port_db};dbname={$database};charset=utf8", $user_db, $pass_db);
    
    

    //Consulta
    $rSql = $restaurantesG != 'ok' ? "AND l.idTipoLugar != :R" : '';
    $sql = '
        SELECT * 
        FROM `lugares` l 
        INNER JOIN `rutas` r ON l.idruta=r.cod 
        INNER JOIN `tiposlugar` t ON l.idTipoLugar=t.cod  
        where r.cod=:cod '.$rSql;
        
    $sentencia = $pdo->prepare($sql);
    $sentencia->bindParam(':cod', $codRuta);
    $R = 'R';
    if($restaurantesG != 'ok') $sentencia->bindParam(':R', $R);
    
    $sentencia->execute();
    $resultados = $sentencia->fetchAll();

    
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}

// Añadiendo sitios
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

// Nombre de la ruta
$nombreRuta = !empty($resultadosR[0]['nombre']) ? $resultadosR[0]['nombre'] : '';
echo "<h1>".$nombreRuta."</h1>";

var_dump($resultados);

// Dar opción al usuario de ver los restaurantes
echo "<form method='get' action='pantalla2.php'>
        <input type='checkbox' id='restaurantes' name='restaurantes' value='ok' />
        <label for='restaurantes'>Quiero restaurantes para comer</label>
        <input type='hidden' name='ruta' value='{$_GET['ruta']}'/>
        <button type='submit'>Enviar</button>
    </form>";

// Variables necesarias para el muestreo de cosas dentro del mapa
$color = '';
$contadorCirculos = 0;
$contadorMarcas= 0;

foreach ($resultados as $key => $r){

    // Mostrando los lugares de la ruta turistica
    if(preg_match("/^R$/", $r['cod']) == 0){
        switch (true) {
            case preg_match("/^T$/", $r['cod']) == 1: $color= 'red'; break;
            case preg_match("/^Z$/", $r['cod']) == 1: $color= 'blue'; break;
            case preg_match("/^C$/", $r['cod']) == 1: $color= 'gray'; break;
            case preg_match("/^P$/", $r['cod']) == 1: $color= 'yellow'; break;
            case preg_match("/^W$/", $r['cod']) == 1: $color= 'brown'; break;
            case preg_match("/^S$/", $r['cod']) == 1: $color= 'green'; break;
            default: $color = 'white';
        }

        $map->addCircle($r['latitud'], $r['longitud'], $color);
        $map->addTooltip(LeafletMaphp::CIRCLE, $contadorCirculos, $r[1]);
        
        $web = empty($r['web']) || $r['web'] == null ? '<a>Sin sitio web</a>' : '<a href="'.$r['web'].'" target="_blank">'.$r['web'].'</a>';
        $info = "pantalla3.php";
        $t = '<b>Web:</b> '.$web.' / <b>Más info:</b> <a href="'.$info.'" target="_blank">'.$info.'</a>';
        $map->addOnClickText(LeafletMaphp::CIRCLE, $contadorCirculos, $t);

        $contadorCirculos++;
    }

    // Muestreo de restaurantes en caso de que quiera el usuario
    if(preg_match("/^R$/", $r['cod']) == 1){
        $map->addMarker($r['latitud'], $r['longitud']);

        $map->addTooltip(LeafletMaphp::MARKER, $contadorMarcas, $r[1]);

        $contadorMarcas++;
    }
}

// Mostrando mapa
echo $map->show();
echo $map->showOnClickDiv();

echo '</body></html>';

?>