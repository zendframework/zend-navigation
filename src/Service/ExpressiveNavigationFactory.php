<?php

namespace Zend\Navigation\Service;

use Zend\Navigation\Page\ExpressivePage;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Navigation\Exception;
use Zend\Navigation\Service\DefaultNavigationFactory as BaseDefaultNavigationFactory;

/**
 * Class ExpressiveNavigationFactory
 */
class ExpressiveNavigationFactory extends BaseDefaultNavigationFactory
{
    /**
     * @inheritdoc
     */
    protected function preparePages(ContainerInterface $container, $pages)
    {
        // Get router
        /** @var RouterInterface $router */
        $router = $container->get(RouterInterface::class);

        return $this->injectComponents($pages, null, $router, null);
    }

    /**
     * @inheritdoc
     */
    protected function injectComponents(
        array $pages,
        $routeMatch = null,
        $router = null,
        $request = null
    ) {
        $this->validateRouter($router);

        foreach ($pages as &$page) {
            if (isset($page['route'])) {
                // Set Expressive page as page type
                $page['type'] = ExpressivePage::class;

                // Set router if exists
                if ($router !== null && ! isset($page['router'])) {
                    $page['router'] = $router;
                }
            }

            if (isset($page['pages'])) {
                $page['pages'] = $this->injectComponents(
                    $page['pages'], $routeMatch, $router, $request
                );
            }
        }

        return $pages;
    }

    /**
     * Validate that a router argument provided to injectComponents is valid.
     *
     * @param null|RouterInterface
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    private function validateRouter($router)
    {
        if (null === $router) {
            return;
        }

        if (! $router instanceof RouterInterface) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s expected by %s::injectComponents; received %s',
                    RouterInterface::class,
                    __CLASS__,
                    (is_object($router) ? get_class($router) : gettype($router))
                )
            );
        }
    }
}
