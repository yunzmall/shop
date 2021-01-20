<?php


namespace app\framework\Model;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use \ArrayAccess;
use \JsonSerializable;

class SimpleModel implements Arrayable, Jsonable, JsonSerializable, ArrayAccess
{
    public $attributes = [];
    public $typeDefaults = [
        'int' => 0,
        'string' => '',
        'timestamp' => 0,
    ];
    public $attributeTypes = [];

    public function formatAttributes()
    {
        $result = [];
        foreach ($this->attributeTypes as $key => $attributeType) {
            switch ($attributeType) {
                case 'timestamp':
                    $result[$key] = date('Y-m-d H:i:s', $this->$key);
                    break;
                default:
                    break;
            }
        }
        $result = array_merge($this->attributes, $result);
        return $result;
    }

    public function __construct($attributes)
    {
        $this->attributes = array_merge($this->defaultAttributes(), $attributes);
    }

    protected function defaultAttributes()
    {
        $attributes = [];
        foreach ($this->attributes as $key => $attribute) {
            $attributes[$key] = $this->typeDefaults[$attribute];
        }
        return $attributes;
    }

    public function __get($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
        if (method_exists($this, 'get' . ucfirst($key) . 'Attribute')) {
            return $this->{'get' . ucfirst($key) . 'Attribute'}();
        }
        return null;
    }

    public function __set($key, $value)
    {
        if (isset($this->attributes[$key])) {
            $this->attributes[$key] = $value;
        }
        if (method_exists($this, 'set' . ucfirst($key) . 'Attribute')) {
            return $this->{'set' . ucfirst($key) . 'Attribute'}($value);
        }
        return null;
    }

    protected function touchAttributes()
    {
        foreach (array_keys($this->attributes) as $key) {
            $this->attributes[$key] = $this->$key;
        }
    }

    public function toArray()
    {
        $this->touchAttributes();

        return $this->formatAttributes();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __toString()
    {
        return $this->toJson();
    }

    public function __isset($key)
    {
        return !is_null($this->attributes[$key]);

    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

}