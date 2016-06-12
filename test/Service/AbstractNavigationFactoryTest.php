<?php
/**
 * @link      http://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Navigation\Service;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionMethod;
use Zend\Mvc\Router as MvcRouter;
use Zend\Navigation\Exception;
use Zend\Router;

/**
 * @todo Write tests covering full functionality. Tests were introduced to
 *     resolve zendframework/zend-navigation#37, and cover one specific
 *     method to ensure argument validation works correctly.
 */
class AbstractNavigationFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->factory = new TestAsset\TestNavigationFactory();
    }

    public function testCanInjectComponentsUsingZendRouterClasses()
    {
        $routeMatch = $this->prophesize(Router\RouteMatch::class)->reveal();
        $router = $this->prophesize(Router\RouteStackInterface::class)->reveal();
        $args = [[], $routeMatch, $router];

        $r = new ReflectionMethod($this->factory, 'injectComponents');
        $r->setAccessible(true);
        try {
            $pages = $r->invokeArgs($this->factory, $args);
        } catch (Exception\InvalidArgumentException $e) {
            $message = sprintf(
                'injectComponents should not raise exception for zend-router classes; received %s',
                $e->getMessage()
            );
            $this->fail($message);
        }

        $this->assertSame([], $pages);
    }

    public function testCanInjectComponentsUsingZendMvcRouterClasses()
    {
        if (! class_exists(MvcRouter\RouteMatch::class)) {
            $this->markTestSkipped('Test does not run for zend-mvc v3 releases');
        }

        $routeMatch = $this->prophesize(MvcRouter\RouteMatch::class)->reveal();
        $router = $this->prophesize(MvcRouter\RouteStackInterface::class)->reveal();
        $args = [[], $routeMatch, $router];

        $r = new ReflectionMethod($this->factory, 'injectComponents');
        $r->setAccessible(true);
        try {
            $pages = $r->invokeArgs($this->factory, $args);
        } catch (Exception\InvalidArgumentException $e) {
            $message = sprintf(
                'injectComponents should not raise exception for zend-mvc router classes; received %s',
                $e->getMessage()
            );
            $this->fail($message);
        }

        $this->assertSame([], $pages);
    }
}
