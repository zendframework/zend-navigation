<?php
/**
 * @see       https://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-navigation/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Navigation\Page;

use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\Exception\RuntimeException as RouterException;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\ZendRouter;
use Zend\Navigation\Exception\InvalidArgumentException;
use Zend\Navigation\Page\ExpressivePage;

class ExpressivePageTest extends TestCase
{
    /**
     * @var \Zend\Expressive\Router\RouterInterface
     */
    private $router;

    /**
     * @var \Zend\Expressive\Router\Route
     */
    private $route;

    /**
     * @var \Zend\Expressive\Router\RouteResult
     */
    private $routeResult;

    protected function setUp()
    {
        $this->route = new Route('/foo', 'foo', ['GET'], 'foo');

        $this->router = new ZendRouter();
        $this->router->addRoute($this->route);

        $this->routeResult = $this->router->match(
            $request = new ServerRequest(
                ['REQUEST_METHOD' => 'GET'],
                [],
                '/foo',
                'GET'
            )
        );
    }

    public function testGetHref()
    {
        $page = new ExpressivePage([
            'route'       => 'foo',
            'router'      => $this->router,
            'routeResult' => $this->routeResult,
        ]);

        $this->assertSame('/foo', $page->getHref());
    }

    public function testGetHrefWithoutRouteName()
    {
        $page = new ExpressivePage([
            'router'      => $this->router,
            'routeResult' => $this->routeResult,
        ]);

        $this->assertSame('/foo', $page->getHref());
    }

    public function testGetHrefWithFragment()
    {
        $page = new ExpressivePage([
            'route'       => 'foo',
            'router'      => $this->router,
            'routeResult' => $this->routeResult,
            'fragment'    => 'bar',
        ]);

        $this->assertSame('/foo#bar', $page->getHref());
    }

    public function testGetHrefWithQueryParams()
    {
        $page = new ExpressivePage([
            'route'       => 'foo',
            'router'      => $this->router,
            'routeResult' => $this->routeResult,
            'query'       => [
                'bar' => 1,
                'baz' => 2,
            ],
        ]);

        $this->assertSame('/foo?bar=1&baz=2', $page->getHref());
    }

    public function testGetHrefWithFailedResultSet()
    {
        $this->expectException(RouterException::class);

        $failedResultSet = RouteResult::fromRouteFailure();
        $page            = new ExpressivePage([
            'router'      => $this->router,
            'routeResult' => $failedResultSet,
        ]);

        $page->getHref();
    }

    public function testGetHrefSetsHrefCache()
    {

        $page = new ExpressivePage([
            'route'       => 'foo',
            'router'      => $this->router,
            'routeResult' => $this->routeResult,
        ]);

        $reflection = new \ReflectionClass($page);
        $property   = $reflection->getProperty('hrefCache');
        $property->setAccessible(true);

        $this->assertNull($property->getValue($page));
        $page->getHref();

        $this->assertSame('/foo', $property->getValue($page));
        $page->getHref();
    }

    public function testIsActive()
    {
        $page = new ExpressivePage([
            'route'       => 'foo',
            'router'      => $this->router,
            'routeResult' => $this->routeResult,
        ]);

        $this->assertTrue($page->isActive());
    }

    public function testIsActiveWithoutRoute()
    {
        $page = new ExpressivePage([
            'router'      => $this->router,
            'routeResult' => $this->routeResult,
        ]);

        $this->assertFalse($page->isActive());
    }

    public function testSetRoutePerConstructor()
    {
        $name = 'foo';
        $page = new ExpressivePage([
            'route' => $name,
        ]);

        $this->assertSame($name, $page->getRoute());
    }

    public function testSetRoutePerMethod()
    {
        $name = 'foo';
        $page = new ExpressivePage();
        $page->setRoute($name);

        $this->assertSame($name, $page->getRoute());
    }

    public function testSetRouteToNull()
    {
        $page = new ExpressivePage();
        $page->setRoute(null);

        $this->assertNull($page->getRoute());
    }

    /**
     * @dataProvider invalidArgumentProvider
     *
     * @param $value
     */
    public function testInvalidArgumentForRouteShouldThrowException($value)
    {
        $this->expectException(InvalidArgumentException::class);

        $page = new ExpressivePage();
        $page->setRoute($value);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function invalidArgumentProvider()
    {
        return [
            [''],
            [new \stdClass()],
            [1],
            [1.0],
            [[]],
        ];
    }

    public function testSetRouterPerConstructor()
    {
        $page = new ExpressivePage([
            'router' => $this->router,
        ]);

        $this->assertSame($this->router, $page->getRouter());
    }

    public function testSetRouterPerMethod()
    {
        $page = new ExpressivePage();
        $page->setRouter($this->router);

        $this->assertSame($this->router, $page->getRouter());
    }

    public function testSetRouteResultPerConstructor()
    {
        $page = new ExpressivePage([
            'routeResult' => $this->routeResult,
        ]);

        $this->assertSame($this->routeResult, $page->getRouteResult());
    }

    public function testSetRouteResultPerMethod()
    {
        $page = new ExpressivePage();
        $page->setRouteResult($this->routeResult);

        $this->assertSame($this->routeResult, $page->getRouteResult());
    }

    public function testSetParamsPerConstructor()
    {
        $params = [
            'foo' => 'bar',
        ];
        $page   = new ExpressivePage([
            'params' => $params,
        ]);

        $this->assertSame($params, $page->getParams());
    }

    public function testSetParamsPerMethod()
    {
        $params = [
            'foo' => 'bar',
        ];
        $page   = new ExpressivePage();
        $page->setParams($params);

        $this->assertSame($params, $page->getParams());
    }

    public function testSetQueryPerConstructor()
    {
        $query = [
            'foo' => 'bar',
        ];
        $page  = new ExpressivePage([
            'query' => $query,
        ]);

        $this->assertSame($query, $page->getQuery());
    }

    public function testSetQueryPerMethod()
    {
        $query = [
            'foo' => 'bar',
        ];
        $page  = new ExpressivePage();
        $page->setQuery($query);

        $this->assertSame($query, $page->getQuery());
    }
}
