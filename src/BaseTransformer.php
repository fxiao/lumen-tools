<?php

namespace Fxiao\LumenTools;

use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Arr;

class BaseTransformer extends TransformerAbstract
{
    protected $withHideFields = [];
    protected $withShowFields = [];
    protected $withParamLimit = 10;
    protected $withParamOrder = ['id', 'desc'];

    public function __construct(ParamBag $params = null)
    {
        if($params) {
            $this->withParamBagShow($params);
        }
    }

    public function transform($models)
    {
        return $this->withFilterFields($models->attributesToArray());
    }

    public function hide(array $fields)
    {
        $this->withHideFields = $fields;
        return $this;
    }

    public function show(array $fields)
    {
        $this->withShowFields = $fields;
        return $this;
    }

    /**
     * include 中字段显示过滤处理
     * URI：xxxx?include=xx:show(field|field|field):hide(field|field|field)
     */
    protected function withParamBagShow(ParamBag $params = null)
    {
        $show_fields = $params->get('show', false);
        $hide_fields = $params->get('hide', false);

        if($show_fields) {
            $this->show($show_fields);
        }

        if($hide_fields) {
            $this->hide($hide_fields);
        }

        return $this;
    }

    protected function withFilterFields(array $array)
    {
        if($this->withShowFields) {
            // 先过滤是否是 相关字段
            $array = Arr::only($array, $this->withShowFields);
        }

        if($this->withHideFields) {
            // 先过滤是否是 相关字段
            $array = Arr::except($array, $this->withHideFields);
        }

        return $array;
    }

    protected function withParamLimit(ParamBag $params = null)
    {
        return $params->get('limit') 
            ? current($params->get('limit')) 
            : $this->withParamLimit;
    }

    protected function withParamOrderFilter($relations, ParamBag $params = null)
    {
        // 排序
        list($orderCol, $orderBy) = $params->get('order') ?? $this->withParamOrder;
        if($orderBy == 'desc') {
            $relations = $relations->sortByDesc($orderCol);
        } else {
            $relations = $relations->sortBy($orderCol);
        }
        $relations = $relations->values();

        // 过滤
        $where = $params->get('where');
        if (is_array($where) && count($where) == 2){
            list($field, $value) = $where;
            $relations = $relations->where($field, $value);
        }
        return $relations;
        
    }

}
