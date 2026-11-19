<?php
//Cadena de conexión a la base de datos
$dns = 'mysql:host=localhost;dbname=pyme11;charset=utf8';

//reglas de seguridad, esta matriz contiene las opciones de configuración para la conexión PDO
$opciones = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
];

//Bloque try-catch para manejar errores de conexión
try {
    //Se crea una nueva instancia de PDO para establecer la conexión a la base de datos
    $pdo = new PDO($dns, 'root', '', $opciones);

} catch (PDOException $e) {
    //Si ocurre un error, se muestra un mensaje y se detiene la ejecución del script
    die('Error de conexión: ' . $e->getMessage());
}
?>