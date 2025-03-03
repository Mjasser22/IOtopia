<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploaderService
{
    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function upload(UploadedFile $file): string
    {
        $fileName = uniqid() . '.' . $file->guessExtension();
        $file->move($this->uploadDir, $fileName);

        return $fileName;
    }
}
