<?php

namespace Zend\Navigation\Middleware;

use RecursiveIteratorIterator;
use Zend\Navigation\Page\ExpressivePage;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Navigation\Navigation;

/**
 * Pipeline middleware for injecting a Navigation with a RouteResult.
 */
class NavigationMiddleware
{
    /**
     * @var Navigation
     */
    private $navigation;

    /**
     * @param Navigation $navigation
     */
    public function __construct(Navigation $navigation)
    {
        $this->navigation = $navigation;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $routeResult = $request->getAttribute(RouteResult::class, false);

        if ($routeResult instanceof RouteResult) {
            $iterator = new RecursiveIteratorIterator(
                $this->navigation,
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $page) {
                if ($page instanceof ExpressivePage) {
                    $page->setRouteResult($routeResult);
                }
            }
        }

        return $next($request, $response);
    }
}
