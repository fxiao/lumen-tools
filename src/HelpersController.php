<?php

namespace Fxiao\LumenTools;

use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Artisan;
use Fxiao\LumenTools\Scaffold\ModelCreator;
use Fxiao\LumenTools\Scaffold\ControllerCreator;
use Fxiao\LumenTools\Scaffold\TransformerCreator;
use Fxiao\LumenTools\Scaffold\MigrationCreator;
use Fxiao\LumenTools\Scaffold\RouteCreator;

class HelpersController extends Controller
{
    protected $msg = false;

    public function index(Request $request)
    {
        /*
        $dbTypes = [
            'string', 'integer', 'text', 'float', 'double', 'decimal', 'boolean', 'date', 'time',
            'dateTime', 'timestamp', 'char', 'mediumText', 'longText', 'tinyInteger', 'smallInteger',
            'mediumInteger', 'bigInteger', 'unsignedTinyInteger', 'unsignedSmallInteger', 'unsignedMediumInteger',
            'unsignedInteger', 'unsignedBigInteger', 'enum', 'json', 'jsonb', 'dateTimeTz', 'timeTz',
            'timestampTz', 'nullableTimestamps', 'binary', 'ipAddress', 'macAddress',
        ];*/

        return app('files')->get(__DIR__.'/dev-helpers.html');
    }

    public function store(Request $request)
    {
        $paths = [];
        $message = '';

        try {

            $table_name = $request->get('table_name'); 
            $class_name = table_to_model($table_name); 
            $relations = $request->get('relations', []);

            $model_name = (env('DEV_HELPERS_MODELS_PATH', 'App\\Models\\') 
                ?: 'App\\Models\\') . $class_name;

            $controller_name = (env('DEV_HELPERS_CONTROLLER_PATH', 'App\\Http\\Controllers\\')
                ?: 'App\\Http\\Controllers\\') . $class_name . 'Controller';

            $transformer_name = (env('DEV_HELPERS_TRANSFORMER_PATH', 'App\\Transformers\\') 
                ?: 'App\\Transformers\\') . ($relations ? $class_name : 'Base') . 'Transformer';

            $route_name = (env('DEV_HELPERS_ROUTE_PATH', 'routes\\')
                ?: 'routes\\') . $table_name;

            // 1. Create model.
            if (in_array('model', $request->get('create'))) {
                $paths['model'] = (new ModelCreator($table_name, $model_name))->create(
                    $request->get('fields'),
                    $request->get('primary_key'),
                    $request->get('timestamps') == 'on',
                    $request->get('soft_deletes') == 'on',
                    $relations
                );
            }

            // 2. Create controller.
            if (in_array('controller', $request->get('create'))) {
                $paths['controller'] = (new ControllerCreator($controller_name, $table_name))
                    ->buildBluePrint($request->get('fields'))
                    ->create($model_name, $transformer_name);
            }

            // 3. Create migration.
            if (in_array('migration', $request->get('create'))) {
                $migrationName = 'create_'.$table_name.'_table';

                $paths['migration'] = (new MigrationCreator(app('files')))->buildBluePrint(
                    $request->get('fields'),
                    $request->get('primary_key', 'id'),
                    $request->get('timestamps') == 'on',
                    $request->get('soft_deletes') == 'on'
                )->create($migrationName, database_path('migrations'), $table_name);
            }

            // 4. Run migrate.
            if (in_array('migrate', $request->get('create'))) {
                Artisan::call('migrate');
                $message = Artisan::output();
            }

            // 5. Create transformer of relation
            if (in_array('transformer', $request->get('create')) && $relations) {
                $paths['transformer'] = (new TransformerCreator($transformer_name))
                    ->create($class_name, $relations);
            }

            // 6. Create route
            if (in_array('route', $request->get('create'))) {

                $paths['route'] = (new RouteCreator($route_name))
                    ->create($controller_name);
            }
        } catch (\Exception $exception) {

            // Delete generated files if exception thrown.
            app('files')->delete($paths);

            dd($exception);
        }

        dump($paths);
        return $this->index($request);
    }

    protected function backWithException(\Exception $exception)
    {
        $error = new MessageBag([
            'title'   => 'Error',
            'message' => $exception->getMessage(),
        ]);

        //return back()->withInput()->with(compact('error'));
    }

    protected function backWithSuccess($paths, $message)
    {
        $messages = [];

        foreach ($paths as $name => $path) {
            $messages[] = ucfirst($name).": $path";
        }

        $messages[] = "<br />$message";

        $success = new MessageBag([
            'title'   => 'Success',
            'message' => implode('<br />', $messages),
        ]);

        //return back()->with(compact('success'));
    }
}
