<?php

namespace ZipStream\Option;

class Archive
{
    protected $comment;
    protected $compressionMethod;
    protected $encryptionMethod;
    protected $enableZip64;
    protected $outputStream;

    public function __construct()
    {
        $this->comment = '';
        $this->compressionMethod = 'deflate'; // Método de compresión por defecto
        $this->encryptionMethod = null; // Sin cifrado por defecto
        $this->enableZip64 = false; // Desactivar Zip64 por defecto
        $this->outputStream = null; // Salida predeterminada
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setCompressionMethod(string $method): self
    {
        $this->compressionMethod = $method;
        return $this;
    }

    public function getCompressionMethod(): string
    {
        return $this->compressionMethod;
    }

    public function setEncryptionMethod(?string $method): self
    {
        $this->encryptionMethod = $method;
        return $this;
    }

    public function getEncryptionMethod(): ?string
    {
        return $this->encryptionMethod;
    }

    public function setEnableZip64(bool $enable): self
    {
        $this->enableZip64 = $enable;
        return $this;
    }

    public function getEnableZip64(): bool
    {
        return $this->enableZip64;
    }

    public function setOutputStream($stream): self
    {
        $this->outputStream = $stream;
        return $this;
    }

    public function getOutputStream()
    {
        return $this->outputStream;
    }
}
