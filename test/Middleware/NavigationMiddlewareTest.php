<?php

namespace ZendTest\Navigation\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Navigation\Exception\InvalidArgumentException;
use Zend\Navigation\Middleware\NavigationMiddleware;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\ExpressivePage;

class NavigationMiddlewareTest extends TestCase
{
    /**
     * @var NavigationMiddleware
     */
    private $middleware;

    /**
     * @var Navigation
     */
    private $navigation;

    protected function setUp()
    {
        $this->navigation = new Navigation(
            [
                new ExpressivePage(['route' => 'home']),
            ]
        );

        $this->middleware = new NavigationMiddleware([$this->navigation]);
    }

    public function testRouteResultShouldAddedToPages()
    {
        // Route result test double
        $routeResult = $this->prophesize(RouteResult::class)->reveal();

        // Request test double
        /** @var ServerRequestInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy     = $this->prophesize(ServerRequestInterface::class);
        $prophecy->getAttribute(RouteResult::class, false)->willReturn(
            $routeResult
        );
        $request = $prophecy->reveal();

        // Delegate test double
        /** @var DelegateInterface $delegate */
        $delegate = $this->prophesize(DelegateInterface::class)->reveal();

        $this->middleware->process($request, $delegate);

        /** @var ExpressivePage $page */
        $page = $this->navigation->findOneBy('route', 'home');
        $this->assertEquals($routeResult, $page->getRouteResult());
    }

    public function testInvalidContainerShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        new NavigationMiddleware([1]);
    }
}
