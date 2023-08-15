<?php
require 'vendor/autoload.php'; // Asegúrate de haber instalado las dependencias con Composer

$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "eventscalendar";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST["titulo"];
    $fecha = $_POST["fecha"];
    $lugar = $_POST["lugar"];
    $descripcion = $_POST["descripcion"];

    // Configura tus credenciales y detalles de Firebase Storage aquí
    $storage = new \Google\Cloud\Storage\StorageClient([
        'projectId' => 'eventcalendar-7e0dc',
        'keyFilePath' => 'eventcalendar-7e0dc-firebase-adminsdk-zbn98-45a95464ad.json'
    ]);
    $bucketName = 'eventcalendar-7e0dc.appspot.com';
    $bucket = $storage->bucket($bucketName);


    function uploadImage($file, $bucket)
    {
        $imageFileName = uniqid() . '-' . $file['name'];
        $imageBlob = file_get_contents($file['tmp_name']);

        $object = $bucket->upload($imageBlob, [
            'name' => $imageFileName
        ]);

        // Obtén la URL de descarga de la imagen
        $imagePublicUrl = $object->signedUrl(new \DateTime('tomorrow'));

        return $imagePublicUrl;
    }

    if (isset($_FILES["imagen1"]) && isset($_FILES["imagen2"])) {
        $imagen1 = $_FILES["imagen1"];
        $imagen2 = $_FILES["imagen2"];

        $urlImagen1 = uploadImage($imagen1, $bucket); // Llamada a la función de cargar imagen
        $urlImagen2 = uploadImage($imagen2, $bucket); // Llamada a la función de cargar imagen

        // Insertar la información del evento y las URLs de las imágenes en la base de datos
        $status = 1; // Nuevo campo "status" con valor 1
        $insertQuery = "INSERT INTO eventos (titulo, fecha, lugar, descripcion, imagen1, imagen2, status)
                        VALUES ('$titulo', '$fecha', '$lugar', '$descripcion', '$urlImagen1', '$urlImagen2', '$status')";

        if (mysqli_query($conn, $insertQuery)) {
            echo "Evento guardado correctamente.";
            $_POST = array();
        } else {
            echo "Error al guardar el evento: " . mysqli_error($conn);
        }
    } else {
        echo "Debes seleccionar dos imágenes.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... Etiquetas meta y título ... -->
</head>
<body>
    <div class="container">
        <h1>Generar Evento</h1>
        <!-- ... Formulario ... -->
        <p class="success-message"><?php echo $successMessage; ?></p>
        <p class="error-message"><?php echo $errorMessage; ?></p>
    </div>
    <script>
        // Redirigir después de mostrar el mensaje y vaciar el formulario
        setTimeout(function() {
            window.location.href = 'generarEvento.php'; // Cambia a la página actual
        }, 3000); // Redirigir después de 3 segundos (ajusta según sea necesario)
    </script>
</body>
</html>