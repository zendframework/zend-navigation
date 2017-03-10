<?php
/**
 * @see       https://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Navigation\Service;

use Psr\Container\ContainerInterface;
use Zend\Navigation\Exception;
use Zend\Navigation\Navigation;

class ExpressiveNavigationFactory extends AbstractExpressiveNavigationFactory
{
    /**
     * @var array
     */
    private $pages;

    /**
     * Create and return a new Navigation instance
     *
     * @param ContainerInterface $container
     * @return Navigation
     */
    public function __invoke(ContainerInterface $container)
    {
        return new Navigation($this->getPages($container));
    }

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws \Zend\Navigation\Exception\InvalidArgumentException
     */
    private function getPages(ContainerInterface $container)
    {
        // Is already created?
        if (null !== $this->pages) {
            return $this->pages;
        }

        $configuration = $container->get('config');

        if (! isset($configuration['navigation'])) {
            throw new Exception\InvalidArgumentException(
                'Could not find navigation configuration key'
            );
        }
        if (! isset($configuration['navigation']['default'])) {
            throw new Exception\InvalidArgumentException(
                'Failed to find a navigation container by the name "default"'
            );
        }

        $pages       = $this->getPagesFromConfig(
            $configuration['navigation']['default']
        );
        $this->pages = $this->preparePages($container, $pages);

        return $this->pages;
    }
}
