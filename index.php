<?php
$host_db = 'localhost';
$port_db = 3306;
$database = 'turismo';
$user_db = 'root';
$pass_db = '';

try {
    // Obteniendo rutas
    $pdo = new PDO("mysql:host={$host_db};port={$port_db};dbname={$database};charset=utf8", $user_db, $pass_db);
    

    //Consulta
    $sql = 'SELECT * FROM `rutas`';
    $sentencia = $pdo->prepare($sql);
    
    $sentencia->execute();
    $resultados = $sentencia->fetchAll();

    
} catch (PDOException $e) {
    print "¡Error!: " . $e->getMessage() . "<br/>";
    die();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Inicio Pantalla 1</title>
</head>
<body>
    <form method="get" action="pantalla2.php">

        <p>
            <label for="ruta">Ruta: </label>
            <select name="ruta" id="ruta">
                <?php foreach ($resultados as $r) : //Rutas a seleccionar
                ?>
                <option value="<?=$r['cod']?>"><?=$r['nombre']?></option>  
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <input type="checkbox" id="restaurantes" name="restaurantes" value="ok" />
            <label for="restaurantes">Quiero restaurantes para comer</label>
        </p>

        <button type="submit">Enviar</button>
    </form>
</body>