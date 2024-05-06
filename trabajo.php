<?php
// Incluir la biblioteca PHPExcel
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "AQUI PONES EL NOMBRE DE TU BASE DE DATOS YA CARGADA ";

// Crear la conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Ruta del archivo Excel
$rutaArchivoExcel = "AQUI PONES EL NOMBRE DE TU EXCEL YA CARGADO.xlsx";

// Cargar el archivo Excel
$documentoExcel = IOFactory::load($rutaArchivoExcel);

// Obtener la hoja activa del archivo Excel
$hoja = $documentoExcel->getActiveSheet();

// Obtener el número total de filas y columnas
$numeroFilas = $hoja->getHighestRow();

// Limitar el número de filas a 10,000 si es mayor
//$numeroFilas = min($numeroFilas, 10000000);

// Iterar sobre las filas del archivo Excel
for ($fila = 2; $fila <= $numeroFilas; $fila++) {
    // Obtener el valor de la celda en la columna "claveArticulo"
    $claveArticulo = $hoja->getCell("A".$fila)->getValue();
    
    // Obtener el valor de la celda en la columna "id_caracteristica"
    $idCaracteristica = $hoja->getCell("B".$fila)->getValue();

    // Obtener el valor de la celda en la columna "valor"
    $campoValor = $hoja->getCell("D".$fila)->getValue();

    // Buscar la claveArticulo en la tabla "articulos"
    $sqlArticulo = "SELECT * FROM articulo WHERE claveArticulo = '$claveArticulo'";
    $resultArticulo = $conn->query($sqlArticulo);
    
    // Si se encuentra el artículo
    if ($resultArticulo->num_rows > 0) {
        // Obtener los campos del artículo
        $rowArticulo = $resultArticulo->fetch_assoc();
        // Obtener el ID del artículo
        $idArticulo = $rowArticulo["idArticulo"];

        // Buscar la clave articulo y el id_caracteristica en la tabla art_caracteristicas
        $sqlArtCaracteristicas = "SELECT * FROM art_caracteristicas WHERE id_articulo = '$idArticulo' AND id_caracteristica = '$idCaracteristica'";
        $resultArtCaracteristicas = $conn->query($sqlArtCaracteristicas);
        
        // Si se encuentra la relación en la tabla art_caracteristicas
        if ($resultArtCaracteristicas->num_rows > 0) {
            // Actualizar el campo valor en la tabla "art_caracteristicas"
            $sqlUpdate = "UPDATE art_caracteristicas SET valor = '$campoValor' WHERE id_articulo = '$idArticulo' AND id_caracteristica = '$idCaracteristica'";
            if ($conn->query($sqlUpdate) === TRUE) {
                echo "Se actualizó el valor para la característica con ID $idCaracteristica y clave de artículo $claveArticulo.<br>";
            } else {
                echo "Error al actualizar el valor: " . $conn->error;
            }
        } else {
            // Crear una nueva relación en la tabla "art_caracteristicas"
            $sqlInsert = "INSERT INTO art_caracteristicas (id_articulo, id_caracteristica, valor) VALUES ('$idArticulo', '$idCaracteristica', '$campoValor')";
            if ($conn->query($sqlInsert) === TRUE) {
                echo "Se creó una nueva relación para la clave de artículo $claveArticulo y la ID de característica $idCaracteristica en la tabla art_caracteristicas.<br>";
            } else {
                echo "Error al crear una nueva relación: " . $conn->error;
            }
        }
    } else {
        echo "No se encontró ningún artículo con la clave de artículo $claveArticulo en la tabla articulo.<br>";
    }
}

// Cerrar la conexión a la base de datos
$conn->close();

?>
