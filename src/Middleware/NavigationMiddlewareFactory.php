<?php

namespace Zend\Navigation\Middleware;

use Interop\Container\ContainerInterface;
use Zend\Navigation\Exception\DomainException;
use Zend\Navigation\Navigation;

class NavigationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(Navigation::class)) {
            throw new DomainException(
                sprintf(
                    '%s requires a %s service at instantiation; none found',
                    NavigationMiddleware::class,
                    Navigation::class
                )
            );
        }

        return new NavigationMiddleware($container->get(Navigation::class));
    }
}
