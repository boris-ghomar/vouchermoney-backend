<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileUploadService
{
    /**
     * Upload file
     * @param UploadedFile $file
     * @param string $directory
     * @return string|null
     */
    public function uploadFile(UploadedFile $file, string $directory): ?string
    {
        return $file->store($directory,'public');
    }

}
