<?php

namespace App\Libraries\Legacy;

use ArrayAccess;

/**
 * CI3-style session access ($this->session->userdata('x') and $this->session->userdata['x']).
 */
class LegacySessionAdapter implements ArrayAccess
{
    public function userdata($key = null)
    {
        if ($key === null) {
            return session()->get();
        }

        return session()->get($key);
    }

    public function offsetExists($offset): bool
    {
        return session()->get($offset) !== null;
    }

    public function offsetGet($offset): mixed
    {
        return session()->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        session()->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        session()->remove($offset);
    }
}
