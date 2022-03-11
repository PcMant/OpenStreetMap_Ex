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
// Nombre de la ruta
$nombreRuta = !empty($resultadosR[0]['nombre']) ? $resultadosR[0]['nombre'] : '';
echo "<h1>".$nombreRuta."</h1>";
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

var_dump($resultados);

// Añadiendo sitios
$map = new LeafletMaphp();

echo $map->showHeadTags();

foreach ($resultados as $r){

    

    switch (true){
        case preg_match("/^T$/",$r['cod']) == 1: $color= 'red';
        case preg_match("/^Z$/",$r['cod']) == 1: $color= 'blue';
        case preg_match("/^C$/",$r['cod']) == 1: $color= 'gray';
        case preg_match("/^P$/",$r['cod']) == 1: $color= 'yellow';
        case preg_match("/^W$/",$r['cod']) == 1: $color= 'brown';
        case preg_match("/^S$/",$r['cod']) == 1: $color= 'green';
        default: $color = 'white';
    }

    $map->addCircle($r['latitud'], $r['longitud'], $color);
    //$map->addMarker($r['latitud'], $r['longitud']);

}

// Mostrando mapa
echo $map->show();

?>