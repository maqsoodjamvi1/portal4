<?php

if (!function_exists('is_active_route')) {
    function is_active_route(string \$routeName, string \$class = 'active'): string
    {
        return \CodeIgniter\Router\Router::path() === route_to(\$routeName, [], 'raw') ? \$class : '';
    }
}
