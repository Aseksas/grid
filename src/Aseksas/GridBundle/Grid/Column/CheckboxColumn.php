<?php
namespace Aseksas\GridBundle\Grid\Column;

class CheckboxColumn extends BaseColumn
{

    public function __construct($index,$name,array $attributeCollection = []) {
        parent::__construct($index,$name,$attributeCollection);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return '<center><input type="checkbox" value="'.$this->value.'" name="'.$this->getIndex().'[]"></center>';
    }
}