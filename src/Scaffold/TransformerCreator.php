<?php

namespace Fxiao\LumenTools\Scaffold;

use Illuminate\Support\Str;

class TransformerCreator
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
    protected $bluePrint = '';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * ControllerCreator constructor.
     *
     * @param string $name
     * @param null   $files
     */
    public function __construct($name, $files = null)
    {
        $this->name = $name;

        $this->files = $files ?: app('files');
    }

    /**
     * Create a controller.
     *
     * @param string $table_name
     *
     * @throws \Exception
     *
     * @return string
     */
    public function create($class_name, $relations)
    {
        $path = $this->getpath($this->name);

        if ($this->files->exists($path)) {
            throw new \Exception("Transformer [$this->name] 已存在!");
        }

        $stub = $this->files->get(__DIR__.'/stubs/transformer.stub');

        $this->replaceFill($stub, $relations)
            ->replaceRelations($stub, $relations, $class_name) 
            ->populateStub($stub, $this->name); 

        $this->files->put($path, $stub);

        return $path;
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
     * Populate stub.
     *
     * @param string $name
     * @param string $stub
     *
     * @return mixed
     */
    protected function populateStub(&$stub, $name)
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        $stub = str_replace(
            ['DummyClass', 'DummyNamespace'],
            [$class, $this->getNamespace($name)],
            $stub
        );

        return $this;
    }

    protected function replaceFill(&$stub, $relations)
    {
        $relations = array_filter($relations, function ($relation) {
            return isset($relation['relation']) && !empty($relation['relation']);
        });

        if (empty($relations)) {
            throw new \Exception('Table relations can\'t be empty');
        }

        $rows_form = []; 
        foreach ($relations as $relation) {
            $class_name = Str::studly(Str::singular($relation['relation']));
            $func_name = lcfirst($class_name);
            $rows_form[] = "'{$func_name}',\n";
        }

        $stub = str_replace('DummyFieldFill', trim(implode(str_repeat(' ', 8), $rows_form), ",\n"), $stub);

        return $this;
    }

    protected function replaceRelations(&$stub, $relations, $class_name)
    {
        $functions = '';

        foreach($relations as $relation) {
            if(in_array($relation['type'], ['hasMany', 'belongsToMany', 'hasManyThrough'])) {
                $functions .= $this->dummyFunctionMany($relation, $class_name);
            } else {
                $functions .= $this->dummyFunctionOne($relation, $class_name);
            }
        }

        $stub = str_replace('DummyRelations', $functions, $stub);

        return $this;
    }

    protected function dummyFunctionOne($relation)
    {
        $class_name = Str::studly(Str::singular($relation['relation']));
        $func_name = lcfirst($class_name);
        $relation_name = $relation['type'];

        return PHP_EOL . PHP_EOL . <<<EOC
    public function include{$class_name}(\$model, ParamBag \$params = null)
    {
        if (!\$model->{$func_name}) {
            return \$this->null();
        }

        return \$this->item(\$model->{$func_name}, new BaseTransformer(\$params));
    }
EOC;
    }

    protected function dummyFunctionMany($relation, $model)
    {
        $class_name = Str::studly(Str::singular($relation['relation']));
        $func_name = lcfirst($class_name);
        $relation_name = $relation['type'];

        return PHP_EOL . PHP_EOL . <<<EOC
    public function include{$class_name}(\$model, ParamBag \$params = null)
    {
        \$limit = \$this->withParamLimit(\$params);
        \$relations = \$this->withParamOrderFilter(\$model->{$func_name}, \$params)->take(\$limit);
        
        return \$this->collection(\$relations, new BaseTransformer(\$params))
            ->setMeta([
                'limit' => \$limit,
                'count' => \$relations->count(),
            ]);
    }
EOC;
    }

}
