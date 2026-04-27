<?php

if (!function_exists('breadcrumb')) {
    function breadcrumb(array $segments)
    {
        $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
        $html .= '<li class="breadcrumb-item"><a href="' . route_to('site_home') . '">Home</a></li>';
        foreach ($segments as $label => $routeName) {
            $url = route_to($routeName);
            $html .= '<li class="breadcrumb-item"><a href="' . $url . '">' . ucfirst($label) . '</a></li>';
        }
        $html .= '</ol></nav>';
        return $html;
    }
}
