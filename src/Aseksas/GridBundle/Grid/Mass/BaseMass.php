<?php
namespace Aseksas\GridBundle\Grid\Mass;

use Aseksas\GridBundle\Grid\Mass\MassListener;

class BaseMass
{

    protected $index = null;
    protected $name = null;
    /**
     * @var $listener MassListener
     */
    protected $listener = null;
    protected $confirm = null;

    public function __construct($index, $name, MassListener $listener, $confirm = null)
    {
        $this->index = $index;
        $this->name = $name;
        $this->listener = $listener;
        $this->confirm = $confirm;
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
     * @return MassListener
     */
    public function getListener(): MassListener
    {
        return $this->listener;
    }

    /**
     * @return null
     */
    public function getConfirm()
    {
        return $this->confirm;
    }


    public function isConfirm()
    {
        return ($this->confirm != null);
    }



}