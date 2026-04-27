<?php

namespace App\Libraries\Restserver\Traits;

trait RestMethods
{
    public function get($key = null)
    {
        return $key === null ? $this->_get_args : ($this->_get_args[$key] ?? null);
    }

    public function post($key = null)
    {
        return $key === null ? $this->_post_args : ($this->_post_args[$key] ?? null);
    }

    public function put($key = null)
    {
        return $key === null ? $this->_put_args : ($this->_put_args[$key] ?? null);
    }

    public function delete($key = null)
    {
        return $key === null ? $this->_delete_args : ($this->_delete_args[$key] ?? null);
    }

    public function patch($key = null)
    {
        return $key === null ? $this->_patch_args : ($this->_patch_args[$key] ?? null);
    }

    public function options($key = null)
    {
        return $key === null ? $this->_options_args : ($this->_options_args[$key] ?? null);
    }

    public function head($key = null)
    {
        return $key === null ? $this->_head_args : ($this->_head_args[$key] ?? null);
    }

    public function query($key = null)
    {
        return $key === null ? $this->_query_args : ($this->_query_args[$key] ?? null);
    }
}
