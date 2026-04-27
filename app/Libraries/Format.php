<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\View\Table;
use Config\Services;
use Exception;

class Format
{
    const ARRAY_FORMAT      = 'array';
    const CSV_FORMAT        = 'csv';
    const JSON_FORMAT       = 'json';
    const HTML_FORMAT       = 'html';
    const PHP_FORMAT        = 'php';
    const SERIALIZED_FORMAT = 'serialized';
    const XML_FORMAT        = 'xml';
    const DEFAULT_FORMAT    = self::JSON_FORMAT;

    protected $request;
    protected $table;
    protected $data       = [];
    protected $from_type  = null;

    public function __construct($data = null, $from_type = null)
    {
        helper('inflector');

        $this->request = Services::request();
        $this->table   = new Table();

        if ($from_type !== null) {
            $method = '_from_' . $from_type;
            if (method_exists($this, $method)) {
                $data = $this->$method($data);
            } else {
                throw new Exception('Format class does not support conversion from "' . $from_type . '".');
            }
        }

        $this->data = $data;
    }

    public static function factory($data, $from_type = null)
    {
        return new static($data, $from_type);
    }

    public function to_array($data = null)
    {
        $data ??= $this->data;

        if (!is_array($data)) {
            $data = (array) $data;
        }

        $array = [];
        foreach ((array) $data as $key => $value) {
            $array[$key] = (is_object($value) || is_array($value)) ? $this->to_array($value) : $value;
        }

        return $array;
    }

    public function to_xml($data = null, $structure = null, $basenode = 'xml')
    {
        $data ??= $this->data;

        if ($structure === null) {
            $structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><{$basenode} />");
        }

        if (!is_array($data) && !is_object($data)) {
            $data = (array) $data;
        }

        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $value = (int) $value;
            }

            if (is_numeric($key)) {
                $key = singular($basenode) !== $basenode ? singular($basenode) : 'item';
            }

            $key = preg_replace('/[^a-z_\-0-9]/i', '', $key);

            if ($key === '_attributes' && (is_array($value) || is_object($value))) {
                $attributes = (array) $value;
                foreach ($attributes as $attrName => $attrValue) {
                    $structure->addAttribute($attrName, $attrValue);
                }
            } elseif (is_array($value) || is_object($value)) {
                $node = $structure->addChild($key);
                $this->to_xml($value, $node, $key);
            } else {
                $structure->addChild($key, htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'));
            }
        }

        return $structure->asXML();
    }

    public function to_html($data = null)
    {
        $data ??= $this->data;
        if (!is_array($data)) {
            $data = (array) $data;
        }

        if (isset($data[0]) && count($data) !== count($data, COUNT_RECURSIVE)) {
            $headings = array_keys($data[0]);
        } else {
            $headings = array_keys($data);
            $data = [$data];
        }

        $this->table->setHeading($headings);

        foreach ($data as $row) {
            $row = @array_map('strval', $row);
            $this->table->addRow($row);
        }

        return $this->table->generate();
    }

    public function to_csv($data = null, $delimiter = ',', $enclosure = '"')
    {
        $handle = fopen('php://temp/maxmemory:1048576', 'w');
        if ($handle === false) {
            return null;
        }

        $data ??= $this->data;

        if (!is_array($data)) {
            $data = (array) $data;
        }

        if (isset($data[0]) && count($data) !== count($data, COUNT_RECURSIVE)) {
            $headings = array_keys($data[0]);
        } else {
            $headings = array_keys($data);
            $data = [$data];
        }

        fputcsv($handle, $headings, $delimiter, $enclosure);

        foreach ($data as $record) {
            if (!is_array($record)) {
                break;
            }
            $record = @array_map('strval', $record);
            fputcsv($handle, $record, $delimiter, $enclosure);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return mb_convert_encoding($csv, 'UTF-16LE', 'UTF-8');
    }

    public function to_json($data = null)
    {
        $data ??= $this->data;
        $callback = $this->request->getGet('callback');

        if (empty($callback)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } elseif (preg_match('/^[a-z_\$][a-z0-9\$_]*(\.[a-z_\$][a-z0-9\$_]*)*$/i', $callback)) {
            return $callback . '(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ');';
        }

        $data['warning'] = 'INVALID JSONP CALLBACK: ' . $callback;
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function to_serialized($data = null)
    {
        $data ??= $this->data;
        return serialize($data);
    }

    public function to_php($data = null)
    {
        $data ??= $this->data;
        return var_export($data, true);
    }

    protected function _from_xml($data)
    {
        return $data ? (array) simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA) : [];
    }

    protected function _from_csv($data, $delimiter = ',', $enclosure = '"')
    {
        return str_getcsv($data, $delimiter, $enclosure);
    }

    protected function _from_json($data)
    {
        return json_decode(trim($data));
    }

    protected function _from_serialize($data)
    {
        return unserialize(trim($data));
    }

    protected function _from_php($data)
    {
        return trim($data);
    }
}
