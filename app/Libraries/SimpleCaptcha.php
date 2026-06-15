<?php

namespace App\Libraries;

class SimpleCaptcha
{
    protected const SESSION_KEY = 'trial_signup_captcha';

    public function generateCode(int $length = 5): string
    {
        $chars  = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max    = strlen($chars) - 1;
        $code   = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, $max)];
        }

        session()->set(self::SESSION_KEY, strtoupper($code));

        return $code;
    }

    public function verify(string $input): bool
    {
        $expected = (string) session()->get(self::SESSION_KEY);
        if ($expected === '') {
            return false;
        }

        $ok = strtoupper(trim($input)) === $expected;
        if ($ok) {
            session()->remove(self::SESSION_KEY);
        }

        return $ok;
    }

    public function renderImage(): void
    {
        $code = $this->generateCode();

        if (! function_exists('imagecreatetruecolor')) {
            header('Content-Type: text/plain; charset=UTF-8');
            echo $code;
            exit;
        }

        $width  = 140;
        $height = 42;
        $image  = imagecreatetruecolor($width, $height);
        $bg     = imagecolorallocate($image, 245, 247, 250);
        $text   = imagecolorallocate($image, 30, 60, 90);
        $noise  = imagecolorallocate($image, 180, 190, 200);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        for ($i = 0; $i < 40; $i++) {
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $noise);
        }

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($code);
        $x = (int) (($width - $textWidth) / 2);
        $y = (int) (($height - imagefontheight($font)) / 2);
        imagestring($image, $font, $x, $y, $code, $text);

        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}
