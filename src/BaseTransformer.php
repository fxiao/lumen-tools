<?php
/**
 * @file BaseTransformer.php
 * @brief Transformer 通用类
 */
namespace Fxiao\LumenTools;

use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Arr;


/**
 * @brief Transformer 通用类
 */
class BaseTransformer extends TransformerAbstract
{
    /**
     * @brief 默认隐藏字段
     */
    protected $withHideFields = [];

    /**
     * @brief 默认只显示字段
     */
    protected $withShowFields = [];

    /**
     * @brief 默认 include 条数
     */
    protected $withParamLimit = 10;

    /**
     * @brief 默认 include 排序
     */
    protected $withParamOrder = ['id', 'desc'];


    public function __construct(ParamBag $params = null)
    {
        if($params) {
            $this->withParamBagShow($params);
        }
    }

    /**
     * @brief 过滤处理
     */
    public function transform($models)
    {
        return $this->withFilterFields($models->attributesToArray());
    }

    /**
     * @brief 隐藏字段处理
     */
    public function hide(array $fields)
    {
        $this->withHideFields = $fields;
        return $this;
    }

    /**
     * @brief 只显示字段处理
     */
    public function show(array $fields)
    {
        $this->withShowFields = $fields;
        return $this;
    }

    /**
     * @brief include 中字段显示过滤处理
     * @detail URI：xxxx?include=xx:show(field|field|field):hide(field|field|field)
     * @param $params include 中的参数
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

    /**
     * @brief include 中字段显示过滤数组处理
     * @param $array 字段名称数组
     */
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

    /**
     * @brief include 中 limit 处理
     * @detail URI：xxxx?include=xx:limit(10)
     */
    protected function withParamLimit(ParamBag $params = null)
    {
        return $params->get('limit') 
            ? current($params->get('limit')) 
            : $this->withParamLimit;
    }

    /**
     * @brief include 中 where 和 order 处理
     * @detail URI：xxxx?include=xx:order(id|desc):where(id|>|2)
     */
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
