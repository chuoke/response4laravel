<?php

namespace Chuoke\Response4Laravel\Tests;

use Chuoke\Response4Laravel\Response;
use Chuoke\Response4Laravel\Tests\Resources\UserResource;
use Chuoke\Response4Laravel\Tests\Resources\UserResourceWithoutWrap;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeDirectory($this->getTempDirectory());

        $this->setUpDatabase($this->app);
        $this->setUpRoutes($this->app);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => $this->getTempDirectory() . DIRECTORY_SEPARATOR . 'database.sqlite',
            'prefix' => '',
        ]);

        config()->set('app.env', 'local');
        config()->set('app.debug', true);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function setUpDatabase($app)
    {
        file_put_contents(config()->get('database.connections.sqlite.database'), null);

        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        foreach (range(1, 10) as $index) {
            User::create(
                [
                    'name' => "user{$index}",
                ]
            );
        }
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function setUpRoutes($app)
    {
        Route::get('/ok', function () {
            return Response::make();
        });

        Route::get('/not-found', function () {
            return Response::make()->notFound();
        });

        Route::get('/not-found/message', function () {
            return Response::make()->notFound('The resource you are looking for does not exist');
        });

        Route::any('/array', function () {
            return Response::make()->ok([
                'id' => 1,
                'name' => 'name',
            ]);
        });

        Route::any('/true', function () {
            return Response::make()->ok(true);
        });

        Route::any('/resource', function () {
            return Response::make()->ok(new UserResource(new User([
                'id' => 1,
                'name' => 'name',
            ])));
        });

        Route::any('/resource/no-wrap', function () {
            return Response::make()->ok(new UserResourceWithoutWrap(new User([
                'id' => 1,
                'name' => 'name',
            ])));
        });

        Route::any('/resource/collection', function () {
            $users = \Illuminate\Database\Eloquent\Collection::make()
                ->push(new User([
                    'id' => 1,
                    'name' => 'name',
                ]))
                ->push(new User([
                    'id' => 2,
                    'name' => 'name2',
                ]));

            return Response::make()->ok(UserResource::collection($users));
        });

        Route::any('/resource/paginator', function (Request $request) {
            $users = User::query()->paginate($request->get('per_page', 2));

            return Response::make()->ok(UserResource::collection($users));
        });

        Route::any('/paginator', function (Request $request) {
            $users = User::query()->paginate($request->get('per_page', 2));

            return Response::make()->ok($users);
        });

        Route::any('/custom/wrap', function () {
            return Response::make()->dataWrap('user')->ok(User::first());
        });

        Route::any('/custom/collection-wrap', function () {
            return Response::make()->collectionDataWrap('users')->ok(User::limit(2)->get());
        });

        Route::any('/custom/response/using', function () {
            return Response::make()
                ->responseUsing(function ($data, $status, $message) {
                    return response()->json([
                        'code' => $status,
                        'message' => $message ?: 'success',
                        'data' => is_array($data) && array_key_exists('data', $data) && count($data) === 1 ? $data['data'] : $data,
                    ], 200);
                })
                ->ok(new UserResource(User::first()));
        });

        Route::any('/custom/response/data-not-array', function () {
            return Response::make()
                ->responseUsing(function ($data, $status, $message) {
                    return response()->json([
                        'code' => $status,
                        'message' => $message ?: 'success',
                        'data' => is_array($data) && array_key_exists('data', $data) ? $data['data'] : $data,
                    ], 200);
                })
                ->ok(true);
        });
    }

    public function getTempDirectory($suffix = '')
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'temp' . ($suffix == '' ? '' : DIRECTORY_SEPARATOR . $suffix);
    }

    protected function initializeDirectory($directory)
    {
        if (!File::isDirectory($directory) || File::deleteDirectory($directory)) {
            // File::makeDirectory($directory);
            File::ensureDirectoryExists($directory);
        }
    }
}
