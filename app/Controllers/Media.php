<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Media extends Controller
{
    public function qbQuestionImage($filename)
    {
        // Security: prevent directory traversal
        $filename = basename($filename);

        $path = WRITEPATH . 'uploads/qb_questions/' . $filename;

        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($path);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Length', filesize($path))
            ->setBody(file_get_contents($path));
    }
}
