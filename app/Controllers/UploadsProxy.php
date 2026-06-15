<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Serves /uploads/{file} from public/uploads when present, otherwise from
 * writable/uploads/student_profiles (fallback when public/uploads is not writable).
 * Keeps base_url('uploads/...') working for student profile images.
 */
class UploadsProxy extends Controller
{
    public function serve(string $segment): ResponseInterface
    {
        $session = session();
        if (! $session->get('member_userid') && ! $session->get('IsAuthorized') && ! $session->get('auth.logged_in')) {
            return $this->response->setStatusCode(403);
        }

        $segment = basename($segment);
        if ($segment === '' || $segment === '.' || $segment === '..' || ! preg_match('/^[A-Za-z0-9._-]+$/', $segment)) {
            return $this->response->setStatusCode(404);
        }

        $paths = [
            rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $segment,
            rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'student_profiles' . DIRECTORY_SEPARATOR . $segment,
        ];

        foreach ($paths as $path) {
            if (! is_file($path)) {
                continue;
            }

            $mime = @mime_content_type($path) ?: 'application/octet-stream';

            $body = @file_get_contents($path);
            if ($body === false) {
                continue;
            }

            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', $mime)
                ->setHeader('Cache-Control', 'public, max-age=86400')
                ->setBody($body);
        }

        return $this->response->setStatusCode(404);
    }
}
