<?php

namespace App\Libraries\Legacy;

class LegacyLoader
{
    /**
     * CI3 compatibility — CI4 already provides session, upload, etc. via services.
     *
     * @param string|list<string> $library
     * @param array<string, mixed>|null $params
     */
    public function library(string|array $library, ?array $params = null, ?string $object_name = null): self
    {
        return $this;
    }

    /**
     * @param string|list<string> $helpers
     */
    public function helper(string|array $helpers): self
    {
        helper(is_array($helpers) ? $helpers : [$helpers]);

        return $this;
    }

    /**
     * @param string $model
     */
    public function model(string $model, string $name = '', bool $db_conn = false): self
    {
        return $this;
    }

    /**
     * Render an admin view (CI3 names map to app/Views/admin/*).
     *
     * @param array<string, mixed> $data
     */
    public function view(string $name, array $data = [], bool $return = false)
    {
        $view = str_contains($name, '/') ? $name : 'admin/' . $name;
        $html = view($view, $data);

        if ($return) {
            return $html;
        }

        echo $html;

        return null;
    }
}
