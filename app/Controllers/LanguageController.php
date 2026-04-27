<?php

namespace App\Controllers;

class LanguageController extends BaseController
{
    public function set($language = 'en')
    {
        // Define allowed languages
        $allowedLanguages = ['en', 'ur', 'ar'];
        
        // Check if the requested language is allowed
        if (!in_array($language, $allowedLanguages)) {
            $language = 'en';
        }
        
        // Store language in session
        session()->set('language', $language);
        
        // Also set a cookie for 1 year
        setcookie('lang', $language, time() + (86400 * 365), '/');
        
        // Return JSON response for AJAX requests
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Language changed to ' . strtoupper($language),
                'language' => $language
            ]);
        }
        
        // Redirect back to previous page
        return redirect()->back();
    }
}