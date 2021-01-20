<?php


namespace app\framework\Entity;


use app\framework\Model\SimpleModel;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class SimpleEntity implements \JsonSerializable,Arrayable,Jsonable
{
    /**
     * @var SimpleModel
     */
    public $model;

    public function toArray()
    {
        return $this->model->attributes;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->model->toArray(), $options);
    }

    public function jsonSerialize()
    {
        return $this->model->toArray();
    }

    public function __toString()
    {
        return $this->toJson();
    }



}