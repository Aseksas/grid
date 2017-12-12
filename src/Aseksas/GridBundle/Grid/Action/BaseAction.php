<?php
namespace Aseksas\GridBundle\Grid\Column;

class BaseAction
{

    protected $index = null;
    protected $name = null;
    protected $attributeCollection = null;

    protected $value = null;

    public function __construct($index, $name, $attributeCollection = [])
    {
        $this->index = $index;
        $this->name = $name;
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * @return null
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSortable()
    {
        return (!isset($this->attributeCollection['sortable']) || $this->attributeCollection['sortable']);
    }

    /**
     * @return bool
     */
    public function isFilterable()
    {
        return (!isset($this->attributeCollection['filterable']) || $this->attributeCollection['filterable']);
    }

    /**
     * @return bool
     */
    public function isSum()
    {
        return (isset($this->attributeCollection['sum']) && $this->attributeCollection['sum']);
    }



}