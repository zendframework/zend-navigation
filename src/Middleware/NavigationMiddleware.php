<?php
/**
 * @see       https://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Navigation\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use RecursiveIteratorIterator;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Exception;
use Zend\Navigation\Page\ExpressivePage;

/**
 * Pipeline middleware for injecting Navigations with a RouteResult.
 */
class NavigationMiddleware implements MiddlewareInterface
{
    /**
     * @var AbstractContainer[]
     */
    private $containers;

    /**
     * @param AbstractContainer[] $containers
     */
    public function __construct(array $containers)
    {
        foreach ($containers as $container) {
            if (! $container instanceof AbstractContainer) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Invalid argument: container must be an instance of %s',
                    AbstractContainer::class
                ));
            }

            $this->containers[] = $container;
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        $routeResult = $request->getAttribute(RouteResult::class, false);

        if ($routeResult instanceof RouteResult) {
            foreach ($this->containers as $container) {
                $iterator = new RecursiveIteratorIterator(
                    $container,
                    RecursiveIteratorIterator::SELF_FIRST
                );

                foreach ($iterator as $page) {
                    if ($page instanceof ExpressivePage) {
                        $page->setRouteResult($routeResult);
                    }
                }
            }
        }

        return $delegate->process($request);
    }
}
