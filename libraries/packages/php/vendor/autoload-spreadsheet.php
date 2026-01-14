<?php
spl_autoload_register(function ($class) {
    // Mapeo para las bibliotecas
    $prefixes = [
        'PhpOffice\\PhpSpreadsheet\\' => __DIR__ . '/PhpOffice/PhpSpreadsheet/',
        'Psr\\SimpleCache\\' => __DIR__ . '/Psr/SimpleCache/',
        'ZipStream\\' => __DIR__ . '/ZipStream/', // Agrega esta línea
    ];

    // Iterar sobre los prefijos
    foreach ($prefixes as $prefix => $dir) {
        // Verificar si la clase usa el prefijo
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            // Obtén el nombre relativo
            $relativeClass = substr($class, $len);
            $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';

            // Incluir el archivo si existe
            if (file_exists($file)) {
                require $file;
            } else {
                echo "Archivo no encontrado: $file\n"; // Para depuración
            }
        }
    }
});