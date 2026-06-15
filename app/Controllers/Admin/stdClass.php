<?php

namespace App\Controllers\Admin;

/**
 * Legacy shim: CI3 controllers use `new stdClass` without a leading backslash.
 * PHP 8.2+ cannot alias the internal stdClass via class_alias().
 */
class stdClass extends \stdClass
{
}
