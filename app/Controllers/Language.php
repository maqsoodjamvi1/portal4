<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Language extends Controller
{
    public function switch($locale)
    {
        $supportedLocales = ['en', 'ur', 'ar'];
        $previousUrl = previous_url();
        
        if (in_array($locale, $supportedLocales)) {
            session()->set('locale', $locale);
            
            // If current URL has a locale, replace it
            if (preg_match('#/(en|ur|ar)/#', $previousUrl)) {
                $newUrl = preg_replace('#/(en|ur|ar)/#', "/$locale/", $previousUrl);
                return redirect()->to($newUrl);
            } else {
                // Add locale to URL
                $baseUrl = base_url();
                $path = str_replace($baseUrl, '', $previousUrl);
                return redirect()->to("$locale$path");
            }
        }
        
        return redirect()->back();
    }
}