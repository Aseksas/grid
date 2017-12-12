<?php
namespace Aseksas\GridBundle\Grid\Mass;

interface MassListener
{
    public function gridMassResponse($grid, $massName, $selectedItemCollection = []);
}