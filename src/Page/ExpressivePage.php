<?php
/**
 * @see       https://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Navigation\Page;

use Zend\Expressive\Router\Exception\RuntimeException as RouterException;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;
use Zend\Navigation\Exception;

class ExpressivePage extends AbstractPage
{
    /**
     * Route name
     *
     * @var string
     */
    private $routeName;

    /**
     * Route parameters
     *
     * @var array
     */
    private $routeParams = [];

    /**
     * @var array
     */
    private $queryParams = [];

    /**
     * @var RouteResult
     */
    private $routeResult;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var string
     */
    private $hrefCache;

    /**
     * @inheritdoc
     */
    public function isActive($recursive = false)
    {
        if (! $this->active && $this->routeName !== null
            && $this->routeResult instanceof RouteResult
        ) {
            $intersectionOfParams = array_intersect_assoc(
                $this->routeResult->getMatchedParams(),
                $this->routeParams
            );

            $matchedRouteName = $this->routeResult->getMatchedRouteName();

            if ($matchedRouteName === $this->routeName
                && count($intersectionOfParams) === count($this->routeParams)
            ) {
                $this->active = true;

                return $this->active;
            }
        }

        return parent::isActive($recursive);
    }

    /**
     * @inheritdoc
     */
    public function getHref()
    {
        // User cache?
        if ($this->hrefCache) {
            return $this->hrefCache;
        }

        if ($this->routeName === null) {
            return $this->generateUriFromResult(
                $this->routeParams,
                $this->routeResult
            );
        }

        // Generate the route
        $href = $this->router->generateUri(
            $this->routeName,
            $this->routeParams
        );

        // Append query parameters if there are any
        if (count($this->queryParams) > 0) {
            $href .= '?' . http_build_query($this->queryParams);
        }

        // Append the fragment identifier
        if ($this->getFragment() !== null) {
            $href .= '#' . $this->getFragment();
        }

        return $this->hrefCache = $href;
    }

    /**
     * @param string|null $route
     */
    public function setRoute($route)
    {
        if (null !== $route && (! is_string($route) || empty($route))) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $route must be a non-empty string or null'
            );
        }

        $this->routeName = $route;
        $this->hrefCache = null;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->routeName;
    }

    /**
     * @param array|null $params
     */
    public function setParams(array $params = null)
    {
        $this->routeParams = empty($params) ? [] : $params;
        $this->hrefCache   = null;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->routeParams;
    }

    /**
     * @param array|null $query
     */
    public function setQuery(array $query = null)
    {
        $this->queryParams = empty($query) ? [] : $query;
        $this->hrefCache   = null;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->queryParams;
    }

    /**
     * @param RouteResult $routeResult
     */
    public function setRouteResult(RouteResult $routeResult)
    {
        $this->routeResult = $routeResult;
    }

    /**
     * @return RouteResult
     */
    public function getRouteResult()
    {
        return $this->routeResult;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param array       $params
     * @param RouteResult $result
     * @return string
     */
    private function generateUriFromResult(array $params, RouteResult $result)
    {
        if ($result->isFailure()) {
            throw new RouterException(
                'Attempting to use matched result when routing failed; aborting'
            );
        }
        $name   = $result->getMatchedRouteName();
        $params = array_merge($result->getMatchedParams(), $params);

        return $this->router->generateUri($name, $params);
    }
}
