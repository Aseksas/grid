<?php
namespace Aseksas\GridBundle\Grid\Column;

class FloatColumn extends BaseColumn
{
    private $precision = null;
    private $mode = PHP_ROUND_HALF_UP;

    public function __construct($index,$name,array $attributeCollection = []) {

        parent::__construct($index,$name,$attributeCollection);

        if(isset($this->attributeCollection['precision'])) {
            $this->precision = (int) $this->attributeCollection['precision'];
        }

        if(isset($this->attributeCollection['mode'])) {
            $mode = $this->attributeCollection['mode'];
            if(in_array($mode, [PHP_ROUND_HALF_UP, PHP_ROUND_HALF_DOWN, PHP_ROUND_HALF_EVEN, PHP_ROUND_HALF_ODD])) {
                $this->mode = (int) $this->attributeCollection['precision'];
            } else {
                throw new \Exception('Round mode not found');
            }
        }
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value)
    {
        $value = (float) $value;
        if($this->precision != null) {
            $value = round($value, $this->precision, $this->mode);
        }
        $this->value = $value;

        return $this;
    }
}