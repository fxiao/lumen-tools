<?php

namespace Fxiao\LumenTools\Scaffold;

class RouteCreator
{
    /**
     * Controller full name.
     *
     * @var string
     */
    protected $name;


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
     * Create a route.
     *
     * @param string $controller_name
     *
     * @throws \Exception
     *
     * @return string
     */
    public function create($controller_name)
    {
        $path = $this->getpath($this->name);

        if ($this->files->exists($path)) {
            throw new \Exception("Route [$this->name] 已存在!");
        }

        $stub = $this->files->get(__DIR__.'/stubs/route.stub');

        $this->files->put($path, $this->populateStub($this->name, $stub, $controller_name));

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

        return app()->basePath() . '/' . implode('/', $segments).'.php';
    }

    /**
     * Populate stub.
     *
     * @param string $name
     * @param string $stub
     * @param string $resource
     *
     * @return mixed
     */
    protected function populateStub($name, $stub, $controller_name)
    {
        $route = str_replace($this->getNamespace($name).'\\', '', $name);
        $class = str_replace($this->getNamespace($controller_name).'\\', '', $controller_name);

        return str_replace(
            ['DummyControllerNamespace', 'DummyRoute', 'DummyPxRoute', 'DummyController'],
            [$this->getNamespace($controller_name), str_replace('_', '-', $route), $route, $class],
            $stub
        );
    }

}
