<?php

namespace Fxiao\LumenTools;

use Illuminate\Http\Request;

trait BaseControllerHelper
{
    protected $route_prefix; // 路由别名的前缀，如 configs.show
    protected $model; // 主模型实例
    protected $transformer; // 主模型对应的 Transformer
    protected $filter_field = 'id'; // 过滤的默认字段
    protected $per_page = 10; // 分页每页的数量，默认为 10

    public function index(Request $request)
    {
        $models = $this->orderFilter($request);

        $per_page = $request->get('per_page', $this->per_page);
        $models = $models->paginate($per_page);

        $response = $this->response->paginator($models, $this->transformer($request));

        if ($request->headers->get('shows') == 'title') {
            $response = $response->addMeta('title', $this->title());
        }

        return $response;
    }

    public function show($id, Request $request)
    {
        $model = $this->model->findOrFail($id);

        return $this->response->item($model, $this->transformer($request));
    }

    public function store(Request $request)
    {
        $validator = $this->storeValidator($request);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $model = $this->model->create($this->fieldOnly($request));

        // 201 with location
        $location = dingo_route('v1', $this->route_prefix.'.show', $model->id);

        return $this->response->item($model, $this->transformer($request))
            ->header('Location', $location)
            ->setStatusCode(201);
    }

    public function update($id, Request $request)
    {
        $validator = $this->updateValidator($request);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $model = $this->model->findOrFail($id);
        $attributes = array_filter($this->fieldOnly($request));

        if ($attributes) {
            $model->update($attributes);
        }

        return $this->response->item($model, $this->transformer($request));
    }

    public function destroy($id)
    {
        $this->model->destroy($id);
        return $this->response->noContent();
    }

    /**
     * 字段相关处理
     */
    protected function transformer(Request $request)
    {
        $transformer = $this->transformer;

        $show_fields = $request->get('show_fields', false);
        $hide_fields = $request->get('hide_fields', false);

        if($show_fields !== false) {
            $transformer = $transformer->show(explode(',', $show_fields));
        }

        if($hide_fields !== false) {
            $transformer = $transformer->hide(explode(',', $hide_fields));
        }

        return $transformer;
    }

    /**
     * 过滤和排序处理
     */
    protected function orderFilter(Request $request)
    {
        $models = $this->model;

        // 排序
        $order_by_field = $request->get('order_by_field', 'id');
        $order_by_type = $request->get('order_by_type', 'desc');
        $multiple_orders = $request->get('multiple_orders', false);

        if(!$multiple_orders !== false) {
            $models = $models->orderBy($order_by_field, $order_by_type);
        } else {
            $mo_list = explode(',', $multiple_orders);
            foreach($mo_list as $mo) {
                $order = explode('|', $mo);
                if(count($order) == 2) {
                    $models = $models->orderBy($order[0], $order[1]);
                }
            }
        }

        // 单字段过滤
        $filter_field = $request->get('filter_field', $this->filter_field);
        $filter_type = $request->get('filter_type', '=');
        $filter_value = $request->get('filter_value', false);
        if($filter_value !== false) {
            if ($filter_type == 'like') {
                $filter_value = "%{$filter_value}%";
            }
            $models = $models->where($filter_field, $filter_type, $filter_value);
        }

        // 多字段过滤
        $multiple_filters = $request->get('multiple_filters', false);
        if($multiple_filters !== false) {
            $mf_list = explode(',', $multiple_filters);
            foreach($mf_list as $mf) {
                $filter = explode('|', $mf);
                if(count($filter) == 3) {
                    if ($filter[1] == 'like') {
                        $filter[2] = "%{$filter[2]}%";
                    }
                    $models = $models->where($filter[0], $filter[1], $filter[2]);
                }
            }
        }

        return $models;
    }

    public function fieldOnly(Request $request)
    {
        return $request;
    }

    public function storeValidator(Request $request)
    {
        return \Validator::make($this->fieldOnly($request), []);
    }

    public function updateValidator(Request $request)
    {
        return \Validator::make($this->fieldOnly($request), []);
    }

}
