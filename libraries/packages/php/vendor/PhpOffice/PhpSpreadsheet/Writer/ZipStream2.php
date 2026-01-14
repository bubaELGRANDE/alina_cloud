<?php

namespace PhpOffice\PhpSpreadsheet\Writer;

use ZipStream\Option\Archive;
use ZipStream\ZipStream;
use ZipStream\OperationMode;
use ZipStream\CompressionMethod;

class ZipStream2
{
    /**
     * @param resource $fileHandle
     */
    public static function newZipStream($fileHandle): ZipStream
    {
        $options = new Archive();
        $options->setEnableZip64(false);
        $options->setOutputStream($fileHandle);

        return new ZipStream(
            operationMode: OperationMode::NORMAL,  // Modo de operación
            comment: '',                           // Comentario (puede dejarse vacío)
            outputStream: $fileHandle,             // Stream de salida
            defaultCompressionMethod: CompressionMethod::DEFLATE, // Método de compresión
            defaultDeflateLevel: 6,                // Nivel de compresión
            enableZip64: false,                    // Deshabilitar ZIP64 si se desea
            defaultEnableZeroHeader: true,         // Ajuste predeterminado de cabecera
            sendHttpHeaders: true,                 // Enviar encabezados HTTP si es necesario
            httpHeaderCallback: null,              // Callback para encabezados HTTP
            outputName: null,                      // Nombre de archivo de salida (puede dejarse como null)
            contentDisposition: 'attachment',      // Disposición del contenido
            contentType: 'application/x-zip',      // Tipo de contenido
            flushOutput: false                     // Controlar si se debe vaciar la salida
        );
    }
}
