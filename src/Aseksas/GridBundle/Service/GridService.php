<?php

namespace Aseksas\GridBundle\Service;

use Aseksas\GridBundle\Grid\Column\BaseColumn;
use Aseksas\GridBundle\Grid\Column\CheckboxColumn;
use Aseksas\GridBundle\Grid\Mass\BaseMass;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class GridService
{
    private $_name = null;
    private $_columnCollection = [];
    private $_highlightCollection = [];
    private $_massCollection = [];
    private $_sumCollection = [];

    private $primary_key = 'id';

    private $_limit = 25;
    private $_offset = 0;

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $__request;

    /**
     * @var mixed|object|\Symfony\Bundle\TwigBundle\TwigEngine
     */
    private $__template;

    /**
     * @var $_builder QueryBuilder
     */
    private $_builder;

    /**
     * GridService constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->__container = $container;
        $this->__request = $this->__container->get('request_stack')->getCurrentRequest();
        $this->__template = $this->__container->get('templating');

    }
    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    private function getRequest(){
        return $this->__request;
    }

    /**
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primary_key;
    }

    /**
     * @param string $primary_key
     * @return $this
     */
    public function setPrimaryKey(string $primary_key)
    {
        $this->primary_key = $primary_key;
        return $this;
    }

    /**
     * @return mixed|object|\Symfony\Bundle\TwigBundle\TwigEngine
     */
    private function getTemplate(){
        return $this->__template;
    }

    /**
     * @return null
     */
    public function getName(){
        return $this->_name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name) {
        $this->_name = $name;
        return $this;
    }

    /**
     * @param BaseColumn $column
     * @return $this
     */
    public function addColumn(BaseColumn $column) {
        $this->_columnCollection[$column->getIndex()] = $column;
        return $this;
    }

    /**
     * @param $index
     * @return $this
     */
    public function removeColumn($index)
    {
        if(isset($this->_columnCollection[$index])) {
            unset($this->_columnCollection[$index]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getColumnCollection()
    {
        return $this->_columnCollection;
    }

    /**
     * @param $index
     * @return mixed|null
     */
    public function getColumnSum($index)
    {
        return isset($this->_sumCollection[$index]) ? $this->_sumCollection[$index] : null;
    }

    /**
     * @return bool
     */
    public function hasSumColumn() {
        return (bool) count($this->_sumCollection);
    }

    /**
     * @param BaseMass $massAction
     * @return $this
     */
    public function addMassAction(BaseMass $massAction){
        $this->_massCollection[$massAction->getIndex()] = $massAction;
        return $this;
    }

    /**
     * @param $index
     * @return $this
     */
    public function removeMassAction($index) {
        if(isset($this->_massCollection[$index])){
            unset($this->_massCollection[$index]);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getMassActionCollection() {
        return $this->_massCollection;
    }

    /**
     * @return bool
     */
    public function hasMassAction() {
        return (bool) count($this->_massCollection);
    }

    /**
     * @return array
     */
    public function getHighlightCollection(): array
    {
        return $this->_highlightCollection;
    }

    /**
     * @param $class
     * @param $function
     * @return $this
     * @internal param array $highlightCollection
     */
    public function addHighlight($class, $function)
    {
        $this->_highlightCollection[$class] = $function;
        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function removeHighlight($class) {
        if(isset($this->_highlightCollection[$class])){
            unset($this->_highlightCollection[$class]);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function hasHighlight() : bool
    {
        return (bool) count($this->_highlightCollection);
    }

    /**
     * @param QueryBuilder $builder
     * @return $this
     */
    public function setSource(QueryBuilder $builder) {
        $this->_builder = $builder;
        return $this;
    }

    /**
     * @return string
     */
    public function build()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            header('Content-Type: application/json');
            echo json_encode($this->getData());
            die;
        }


        return $this->getTemplate()->render('@AseksasGrid/Grid/layout.html.twig', ['grid' => $this]);
    }


    private function getData()
    {
        $dataRequest = clone $this->_builder;

        $total = $this->_builder->select('COUNT('.$this->_builder->getRootAlias().'.'.$this->getPrimaryKey().')')->getQuery()->getSingleScalarResult();

        // fix sort column START;
        $this->_sortExec($dataRequest);
        // fix sort END;

        $this->_limit = (int) $this->getRequest()->request->get('length', $this->_limit);
        $this->_offset = (int) $this->getRequest()->request->get('start', $this->_offset);

        $dataRequest->setFirstResult($this->_offset);
        $dataRequest->setMaxResults($this->_limit);

        $result = $dataRequest->getQuery()->getArrayResult();
        $response = [];
        $highlightCollection = [];
        if($total) {
            foreach ($result as $row) {
                $rowResult = [];

                if($this->hasMassAction()) {
                    $rowResult[] = (new CheckboxColumn('ids','#'))
                        ->setValue($this->parseRelation($row, $this->getPrimaryKey()))
                        ->getValue();
                }

                if($this->hasHighlight()) {
                    foreach ($this->getHighlightCollection() as $class => $function) {
                        if($function($row)) {
                            $hhString = isset($highlightCollection[count($response)]) ? $highlightCollection[count($response)] : '';
                            $highlightCollection[(string) count($response)] = $hhString.' '.$class;
                        }
                    }
                }


                /**
                 * @var $obj BaseColumn
                 */
                foreach ($this->getColumnCollection() as $column => $obj) {
                    $item = clone $obj->setValue($this->parseRelation($row, $column));

                    if($item->isSum()) {
                        if(!isset($this->_sumCollection[$obj->getIndex()])) {
                            $this->_sumCollection[$obj->getIndex()] = 0;
                        }
                        $this->_sumCollection[$obj->getIndex()] += $item->getValue();
                    }

                    $rowResult[] = $item->getValue();

                }
                $response[] = $rowResult;
            }
        }

        if($this->hasSumColumn()) {
            $sumRow = [];
            if($this->hasMassAction()) { $sumRow[] = null;  }
            foreach ($this->getColumnCollection() as $item) {
                $sumRow[] = $this->getColumnSum($item->getIndex());
            }
            $response[] = $sumRow;
        }

        return [
            'data' => $response,
            'highlight' => $highlightCollection,
            'recordsTotal' => (int)$total,
            'recordsFiltered' => (int) $total
        ];
    }

    public function parseRelation($array, $item)
    {

        $elementCollection = is_array($item) ? $item : explode(".", $item);

        $response = null;
        foreach ($elementCollection as $element) {
            if ($response == null && isset($array[$element])) {
                $response = $array[$element];
            } else if(isset($response[$element])) {
                $response = $response[$element];
            }
        }

        return $response;
    }

    private function _sortExec(QueryBuilder $dataRequest)
    {
        $sortColumn = 0;
        $sortOrder = 'ASC';
        if($this->getRequest()->request->has('order')) {
            list($order, ) = $this->getRequest()->request->get('order');
            $sortColumn = $order['column'];
            $sortOrder = strtoupper($order['dir']);
            if($this->hasMassAction()) {
                $sortColumn--;
            }
        }
        $arrayValueCollection = array_values($this->getColumnCollection());
        $sortColumn = $arrayValueCollection[$sortColumn]->getIndex();

        list($rootAlias, ) = $dataRequest->getRootAliases();

        if(strpos($sortColumn, '.') === false) {
            $sortColumn = $rootAlias.'.'.$sortColumn;
        }
        $dataRequest->orderBy($sortColumn, $sortOrder);
    }

}
