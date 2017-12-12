<?php
namespace Aseksas\GridBundle\Grid\Column;

class IntColumn extends BaseColumn
{

    public function setValue($value)
    {
        $this->value = (int) $value;
        return $this;
    }
}