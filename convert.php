<?php
// Verificar si se han recibido imágenes
if(isset($_FILES['images'])){
    $response = []; // Array para almacenar la respuesta

    // Directorio de destino para guardar las imágenes convertidas
    $uploadDir = 'C:/MAMP/htdocs/YSAAS/uploads/';
    if(!is_dir($uploadDir)){
        mkdir($uploadDir, 0777, true); // Crear el directorio si no existe
    }

    // Recorrer cada imagen recibida
    foreach($_FILES['images']['tmp_name'] as $key => $tmpName){
        $originalName = $_FILES['images']['name'][$key];
        $fileType = pathinfo($originalName, PATHINFO_EXTENSION);

        // Generar un nombre único para el archivo basado en un código de referencia
        $referenceCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
        $newFileName = $referenceCode . '.' . $fileType;

        $filePath = $uploadDir . $newFileName;

        // Mover el archivo a la ubicación de destino
        if(move_uploaded_file($tmpName, $filePath)){
            // Convertir la imagen a WebP si es JPEG o PNG
            if($fileType == 'jpg' || $fileType == 'jpeg' || $fileType == 'png'){
                $webpName = pathinfo($filePath, PATHINFO_FILENAME) . '.webp';
                $webpPath = $uploadDir . $webpName;

                // Crear objeto Imagick y cargar la imagen
                $imagick = new Imagick($filePath);

                // Convertir la imagen a WebP
                if($imagick->setImageFormat('webp')){
                    $imagick->writeImage($webpPath);

                    // Liberar recursos de Imagick
                    $imagick->clear();
                    $imagick->destroy();

                    // Generar la URL del archivo WebP convertido
                    $webpUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $webpPath;

                    // Agregar la miniatura y la URL a la respuesta
                    $response[] = [
                        'success' => true,
                        'original_name' => $originalName,
                        'new_name' => $newFileName,
                        'webp_name' => $webpName,
                        'webp_url' => $webpUrl
                    ];
                } else {
                    // Si falla la conversión a WebP, agregar un mensaje de error a la respuesta
                    $response[] = [
                        'success' => false,
                        'message' => "Failed to convert '{$originalName}' to WebP."
                    ];
                }
            } else {
                // Si el archivo no es JPEG o PNG, agregar un mensaje de error a la respuesta
                $response[] = [
                    'success' => false,
                    'message' => "File '{$originalName}' is not a valid JPEG or PNG image."
                ];
            }
        } else {
            // Si falla la subida del archivo, agregar un mensaje de error a la respuesta
            $response[] = [
                'success' => false,
                'message' => "Failed to upload '{$originalName}'"
            ];
        }
    }

    // Devolver la respuesta como JSON al front-end
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Si no se han recibido imágenes, devolver un mensaje de error
    header('HTTP/1.1 400 Bad Request');
    echo 'No images uploaded.';
}
?>
