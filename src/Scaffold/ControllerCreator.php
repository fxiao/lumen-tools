<?php

namespace Fxiao\LumenTools\Scaffold;

class ControllerCreator
{
    /**
     * Controller full name.
     *
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $bluePrintForm = '';
    protected $bluePrintJson = '';
    protected $bluePrintTitle = '';
    protected $bluePrintStoreRule = '';
    protected $bluePrintUpdateRule = '';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    protected $tableName;

    /**
     * ControllerCreator constructor.
     *
     * @param string $name
     * @param null   $files
     */
    public function __construct($name, $table_name, $files = null)
    {
        $this->name = $name;

        $this->tableName = $table_name;

        $this->files = $files ?: app('files');
    }

    /**
     * Create a controller.
     *
     * @param string $model
     *
     * @param string $transformer_name
     *
     * @throws \Exception
     *
     * @return string
     */
    public function create($model, $transformer_name)
    {
        $path = $this->getpath($this->name);

        if ($this->files->exists($path)) {
            throw new \Exception("Controller [$this->name] 已存在!");
        }

        $stub = $this->files->get($this->getStub());

        $this->files->put($path, $this->replace($stub, $this->name, $model, $transformer_name));

        return $path;
    }

    /**
     * @param string $stub
     * @param string $name
     * @param string $model
     *
     * @return string
     */
    protected function replace($stub, $name, $model, $transformer_name)
    {
        $stub = $this->replaceClass($stub, $name);

        return str_replace([
            'DummyModelNamespace', 
            'DummyModel', 
            'DummyTableName', 
            'DummyTransformerNamespace', 
            'DummyTransformer',  
            'DummyfieldOnly', 
            'DummyTitle',
            'DummyStoreRule',
            'DummyUpdateRule',
            'DummyJson'
        ], [
            $model, 
            class_basename($model), 
            $this->tableName, 
            $transformer_name, 
            class_basename($transformer_name),  
            $this->bluePrintForm, 
            $this->bluePrintTitle,
            $this->bluePrintStoreRule,
            $this->bluePrintUpdateRule,
            $this->bluePrintJson
        ],
            $stub
        );
    }

    /**
     * Get controller namespace from giving name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @param string $name
     *
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace([
            'DummyClass', 
            'DummyNamespace'
        ], [
            $class, 
            $this->getNamespace($name)
        ], $stub);
    }

    /**
     * Get file path from giving controller name.
     *
     * @param $name
     *
     * @return string
     */
    public function getPath($name)
    {
        $segments = explode('\\', $name);

        array_shift($segments);

        return app()->path(). '/' . implode('/', $segments).'.php';
    }

    /**
     * Get stub file path.
     *
     * @return string
     */
    public function getStub()
    {
        return __DIR__.'/stubs/controller.stub';
    }

    /**
     * Build the table blueprint.
     *
     * @param array      $fields
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function buildBluePrint($fields = [])
    {
        $fields = array_filter($fields, function ($field) {
            return isset($field['name']) && !empty($field['name']);
        });

        if (empty($fields)) {
            throw new \Exception('Table fields can\'t be empty');
        }

        $rows_form = []; 
        $rows_json = []; 
        $rows_title = []; 
        $rows_store_rule = [];
        $rows_update_rule = [];

        foreach ($fields as $field) {
            $rows_form[] = "'{$field['name']}',\n";
            $rows_json[] = "\"{$field['name']}\":\"{$field['comment']}\",\n";
            $rows_title[] = "'{$field['name']}' => '{$field['comment']}',\n";

            $field_type = in_array($field['type'], [
                'integer', 'tinyInteger', 'smallInteger', 'mediumInteger', 'bigInteger', 'unsignedTinyInteger', 'unsignedSmallInteger', 'unsignedMediumInteger', 'unsignedInteger', 'unsignedBigInteger'
            ]) ? 'integer': 'string';
            $rows_store_rule[] = "'{$field['name']}' => '" 
                . (array_get($field, 'nullable') == 'on' ? '' : 'required|')
                . "{$field_type}',\n";
            $rows_update_rule[] = "'{$field['name']}' => '{$field_type}',\n";
        }

        $this->bluePrintForm = trim(implode(str_repeat(' ', 12), $rows_form), ",\n");
        $this->bluePrintJson = trim(implode(str_repeat(' ', 4), $rows_json), ",\n");
        $this->bluePrintTitle = trim(implode(str_repeat(' ', 12), $rows_title), "\n");

        $this->bluePrintStoreRule = trim(implode(str_repeat(' ', 12), $rows_store_rule), "\n");
        $this->bluePrintUpdateRule = trim(implode(str_repeat(' ', 12), $rows_update_rule), "\n");

        return $this;
    }
}

