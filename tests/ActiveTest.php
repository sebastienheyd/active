<?php

namespace SebastienHeyd\ActiveTest;

use SebastienHeyd\Active\Active;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Http\Request;
use Orchestra\Testbench\TestCase;

class ActiveTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        app('router')->group(['middleware' => ['dump']], function () {
            app('router')->get('/foo/bar', ['as' => 'foo.bar', 'uses' => '\SebastienHeyd\ActiveTest\Http\DumpController@indexMethod']);
            app('router')->get('/foo/bar/{id}/view', ['as' => 'foo.bar.view', 'uses' => '\SebastienHeyd\ActiveTest\Http\DumpController@viewMethod']);
            app('router')->get('/home', ['as' => 'home', 'uses' => function () {}]);
            app('router')->get('/', function () {});
            app('router')->bind('model', function ($id) { return new StubModel(['uid' => $id]);});
            app('router')->get('/model/{model}', '\SebastienHeyd\ActiveTest\Http\DumpController@viewMethod');
        });
    }

    public function testReturnCorrectValueWhenNotInitiated()
    {
        $active = new Active(null);

        $this->assertFalse($active->checkAction([]));
        $this->assertFalse($active->checkRouteParam('', ''));
        $this->assertFalse($active->checkRoute([]));
        $this->assertFalse($active->checkRoutePattern([]));
        $this->assertFalse($active->checkUriPattern([]));
        $this->assertFalse($active->checkUri([]));
        $this->assertFalse($active->checkQuery('', ''));
        $this->assertFalse($active->checkController([]));
        $this->assertSame('', $active->getAction());
        $this->assertSame('', $active->getController());
        $this->assertSame('', $active->getMethod());
    }

    public function testGetCorrectClassWithCondition()
    {
        $active = new Active(null);

        $this->assertSame('active', $active->getClassIf(true));
        config(['active.class' => 'active-from-config']);
        $this->assertSame('active-from-config', $active->getClassIf(true));
        $this->assertSame('selected', $active->getClassIf(true, 'selected'));
        $this->assertSame('not-checked', $active->getClassIf(false, 'selected', 'not-checked'));
    }

    /**
     * @dataProvider provideGetActionTestData
     */
    public function testGetCorrectAction(): void
    {
        foreach ($this->provideGetActionTestData() as $testName => [$request, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::getAction(), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->getAction(), "Failed assertion for test: {$testName}");
            $this->assertSame($result, current_action(), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideGetMethodTestData
     */
    public function testGetCorrectMethod(): void
    {
        foreach ($this->provideGetMethodTestData() as $testName => [$request, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::getMethod(), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->getMethod(), "Failed assertion for test: {$testName}");
            $this->assertSame($result, current_method(), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideGetControllerTestData
     */
    public function testGetCorrectController(): void
    {
        foreach ($this->provideGetControllerTestData() as $testName => [$request, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::getController(), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->getController(), "Failed assertion for test: {$testName}");
            $this->assertSame($result, current_controller(), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideCheckActionTestData
     */
    public function testCheckCurrentAction(): void
    {
        foreach ($this->provideCheckActionTestData() as $testName => [$request, $actions, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::checkAction($actions), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->checkAction($actions), "Failed assertion for test: {$testName}");
            $this->assertSame($result, if_action($actions), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideCheckControllerTestData
     */
    public function testCheckCurrentController(): void
    {
        foreach ($this->provideCheckControllerTestData() as $testName => [$request, $controllers, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::checkController($controllers), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->checkController($controllers), "Failed assertion for test: {$testName}");
            $this->assertSame($result, if_controller($controllers), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideCheckRouteTestData
     */
    public function testCheckCurrentRoute(): void
    {
        foreach ($this->provideCheckRouteTestData() as $testName => [$request, $routes, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::checkRoute($routes), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->checkRoute($routes), "Failed assertion for test: {$testName}");
            $this->assertSame($result, if_route($routes), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideCheckRoutePatternTestData
     */
    public function testCheckCurrentRoutePattern(): void
    {
        foreach ($this->provideCheckRoutePatternTestData() as $testName => [$request, $routes, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::checkRoutePattern($routes), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->checkRoutePattern($routes), "Failed assertion for test: {$testName}");
            $this->assertSame($result, if_route_pattern($routes), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideCheckRouteParameterTestData
     */
    public function testCheckCurrentRouteParameter(): void
    {
        foreach ($this->provideCheckRouteParameterTestData() as $testName => [$request, $key, $value, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::checkRouteParam($key, $value), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->checkRouteParam($key, $value), "Failed assertion for test: {$testName}");
            $this->assertSame($result, if_route_param($key, $value), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideCheckUriTestData
     */
    public function testCheckCurrentUri(): void
    {
        foreach ($this->provideCheckUriTestData() as $testName => [$request, $uri, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::checkUri($uri), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->checkUri($uri), "Failed assertion for test: {$testName}");
            $this->assertSame($result, if_uri($uri), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideCheckUriPatternTestData
     */
    public function testCheckCurrentUriPattern(): void
    {
        foreach ($this->provideCheckUriPatternTestData() as $testName => [$request, $uri, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::checkUriPattern($uri), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->checkUriPattern($uri), "Failed assertion for test: {$testName}");
            $this->assertSame($result, if_uri_pattern($uri), "Failed assertion for test: {$testName}");
        }
    }

    /**
     * @dataProvider provideCheckQueryTestData
     */
    public function testCheckCurrentQuerystring(): void
    {
        foreach ($this->provideCheckQueryTestData() as $testName => [$request, $key, $value, $result]) {
            app(HttpKernelContract::class)->handle($request);

            $this->assertSame($result, \Active::checkQuery($key, $value), "Failed assertion for test: {$testName}");
            $this->assertSame($result, app('active')->checkQuery($key, $value), "Failed assertion for test: {$testName}");
            $this->assertSame($result, if_query($key, $value), "Failed assertion for test: {$testName}");
        }
    }

    public function testAliasAndHelperFunctions()
    {
        $this->assertSame('active', \Active::getClassIf(true));
        $this->assertSame('active', active_class(true));
        config(['active.class' => 'active-from-config']);
        $this->assertSame('active-from-config', \Active::getClassIf(true));
        $this->assertSame('active-from-config', active_class(true));
    }

    public static function provideGetActionTestData()
    {
        return [
            'action is a controller method' => [
                Request::create('/foo/bar'),
                '\SebastienHeyd\ActiveTest\Http\DumpController@indexMethod',
            ],
            'action is a closure'           => [
                Request::create('/home'),
                'Closure',
            ],
        ];
    }

    public static function provideGetMethodTestData()
    {
        return [
            'method is a controller method'                          => [
                Request::create('/foo/bar'),
                'indexMethod',
            ],
            'method is a controller method and the route has params' => [
                Request::create('/foo/bar/1/view'),
                'viewMethod',
            ],
            'method is a closure'                                    => [
                Request::create('/home'),
                '',
            ],
        ];
    }

    public static function provideGetControllerTestData()
    {
        return [
            'controller is a controller method' => [
                Request::create('/foo/bar'),
                '\SebastienHeyd\ActiveTest\Http\DumpController',
            ],
            'controller is a closure'           => [
                Request::create('/home'),
                'Closure',
            ],
        ];
    }

    public static function provideCheckActionTestData()
    {
        return [
            'match the first inputted actions'  => [
                Request::create('/foo/bar'),
                '\SebastienHeyd\ActiveTest\Http\DumpController@indexMethod',
                true,
            ],
            'match the second inputted actions' => [
                Request::create('/foo/bar'),
                [
                    '\SebastienHeyd\ActiveTest\Http\DumpController@viewMethod',
                    '\SebastienHeyd\ActiveTest\Http\DumpController@indexMethod',
                ],
                true,
            ],
            'match no action'                   => [
                Request::create('/foo/bar'),
                [
                    '\SebastienHeyd\ActiveTest\Http\DumpController@viewMethod',
                    '\SebastienHeyd\ActiveTest\Http\DumpController@deleteMethod',
                ],
                false,
            ],
        ];
    }

    public static function provideCheckControllerTestData()
    {
        return [
            'match the first inputted controllers'  => [
                Request::create('/foo/bar'),
                '\SebastienHeyd\ActiveTest\Http\DumpController',
                true,
            ],
            'match the second inputted controllers' => [
                Request::create('/foo/bar'),
                ['Namespace\Child\Controller', '\SebastienHeyd\ActiveTest\Http\DumpController'],
                true,
            ],
            'match no controller'                   => [
                Request::create('/foo/bar'),
                ['Controller', 'Namespace\Child\Controller'],
                false,
            ],
        ];
    }

    public static function provideCheckRouteTestData()
    {
        return [
            'match the first inputted route names'  => [
                Request::create('/foo/bar'),
                'foo.bar',
                true,
            ],
            'match the second inputted route names' => [
                Request::create('/foo/bar'),
                ['foo.bar.view', 'foo.bar'],
                true,
            ],
            'match no route name'                   => [
                Request::create('/foo/bar'),
                ['foo.bar.view', 'foo.bar.delete'],
                false,
            ],
            'route with no name'                    => [
                Request::create('/'),
                ['foo.bar.view', null],
                true,
            ],
        ];
    }

    public static function provideCheckRouteParameterTestData()
    {
        return [
            'key value is matched'                     => [
                Request::create('/foo/bar/1/view'),
                'id',
                '1',
                true,
            ],
            'key does not exist'                       => [
                Request::create('/foo/bar/1/view'),
                'foo',
                '1',
                false,
            ],
            'key value is not matched'                 => [
                Request::create('/foo/bar/1/view'),
                'id',
                '2',
                false,
            ],
            'match a route bound to a model'           => [
                Request::create('/model/100'),
                'model',
                '100',
                true,
            ],
            'not match a route bound to another model' => [
                Request::create('/model/100'),
                'model',
                '1',
                false,
            ],
        ];
    }

    public static function provideCheckRoutePatternTestData()
    {
        return [
            'match the first inputted route patterns'  => [
                Request::create('/foo/bar'),
                'foo.*',
                true,
            ],
            'match the second inputted route patterns' => [
                Request::create('/foo/bar'),
                ['bar.*', 'foo.*'],
                true,
            ],
            'match no route pattern'                   => [
                Request::create('/foo/bar'),
                ['bar.*', 'baz.*'],
                false,
            ],
            'route with no name'                       => [
                Request::create('/'),
                ['foo.*', null],
                true,
            ],
        ];
    }

    public static function provideCheckUriTestData()
    {
        return [
            'match the first inputted uri'  => [
                Request::create('/foo/bar'),
                'foo/bar',
                true,
            ],
            'match the second inputted uri' => [
                Request::create('/foo/bar'),
                ['/foo/bar/view', 'foo/bar'],
                true,
            ],
            'match no uri'                  => [
                Request::create('/foo/bar'),
                ['/foo/bar', '/foo/bar/delete'],
                false,
            ],
            'root route'                    => [
                Request::create('/'),
                ['/'],
                true,
            ],
        ];
    }

    public static function provideCheckQueryTestData()
    {
        return [
            'key value is matched'                                      => [
                Request::create('/foo/bar', 'GET', ['id' => 1]),
                'id',
                '1',
                true,
            ],
            'key exists'                                                => [
                Request::create('/foo/bar', 'GET', ['id' => 1]),
                'id',
                false,
                true,
            ],
            'key does not exist'                                        => [
                Request::create('/foo/bar'),
                'foo',
                '1',
                false,
            ],
            'key value is not matched'                                  => [
                Request::create('/foo/bar', 'GET', ['id' => 1]),
                'id',
                '2',
                false,
            ],
            'key is an array that contains the input with wrong type'   => [
                Request::create('/foo/bar', 'GET', ['id' => [1, 2]]),
                'id',
                '2',
                true,
            ],
            'key is an array that contains the input with correct type' => [
                Request::create('/foo/bar', 'GET', ['id' => [1, 2]]),
                'id',
                2,
                true,
            ],
            'key is an array that does not contain the input'           => [
                Request::create('/foo/bar', 'GET', ['id' => [1, 2]]),
                'id',
                '3',
                false,
            ],
        ];
    }

    public static function provideCheckUriPatternTestData()
    {
        return [
            'match the first inputted uri patterns'  => [
                Request::create('/foo/bar'),
                'foo/*',
                true,
            ],
            'match the second inputted uri patterns' => [
                Request::create('/foo/bar'),
                ['bar/*', 'foo/*'],
                true,
            ],
            'match no uri pattern'                   => [
                Request::create('/foo/bar'),
                ['bar/*', 'baz/*'],
                false,
            ],
        ];
    }

    protected function getPackageProviders($app)
    {
        return [
            \SebastienHeyd\Active\ActiveServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Active' => \SebastienHeyd\Active\Facades\Active::class,
        ];
    }

    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', Http\Kernel::class);
    }
}
