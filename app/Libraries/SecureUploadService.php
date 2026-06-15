<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Validates and stores uploaded files with MIME/size checks.
 */
class SecureUploadService
{
    /**
     * @param list<string> $allowedMimePrefixes e.g. ['image/']
     */
    public function storeImage(
        UploadedFile $file,
        string $directory,
        int $maxKb = 2048,
        array $allowedMimePrefixes = ['image/'],
    ): ?string {
        if (! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        if ($file->getSizeByUnit('kb') > $maxKb) {
            return null;
        }

        $mime = (string) $file->getMimeType();
        $ok   = false;
        foreach ($allowedMimePrefixes as $prefix) {
            if (str_starts_with($mime, $prefix)) {
                $ok = true;
                break;
            }
        }
        if (! $ok) {
            return null;
        }

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $newName = $file->getRandomName();
        if (! $file->move($directory, $newName)) {
            return null;
        }

        return $newName;
    }
}
