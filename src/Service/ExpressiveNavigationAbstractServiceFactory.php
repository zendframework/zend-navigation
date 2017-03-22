<?php
/**
 * @see       https://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-navigation/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Navigation\Service;

use Interop\Container\ContainerInterface;
use Zend\Navigation\Navigation;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

final class ExpressiveNavigationAbstractServiceFactory extends AbstractExpressiveNavigationFactory implements
    AbstractFactoryInterface
{
    /**
     * Top-level configuration key indicating navigation configuration
     *
     * @var string
     */
    const CONFIG_KEY = 'navigation';

    /**
     * Service manager factory prefix
     *
     * @var string
     */
    const SERVICE_PREFIX = 'Zend\\Navigation\\';

    /**
     * Navigation configuration
     *
     * @var array|null
     */
    private $config;

    /**
     * @var Navigation[]
     */
    private $containers = [];

    /**
     * Can we create a navigation by the requested name?
     *
     * @param ContainerInterface $container
     * @param string             $requestedName Name by which service was
     *                                          requested, must start with
     *                                          Zend\Navigation\
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $requestedName = $this->normalizeRequestedName($requestedName);

        if (0 !== strpos($requestedName, self::SERVICE_PREFIX)) {
            return false;
        }

        if (array_key_exists($requestedName, $this->containers)) {
            return true;
        }

        $config = $this->getConfig($container);

        return $this->hasNamedConfig($requestedName, $config);
    }

    /**
     * {@inheritDoc}
     *
     * @return Navigation
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ) {
        $requestedName = $this->normalizeRequestedName($requestedName);

        // Is already created?
        if (array_key_exists($requestedName, $this->containers)) {
            return $this->containers[$requestedName];
        }

        // Get config
        $config          = $this->getConfig($container);
        $pagesFromConfig = $this->getPagesFromConfig(
            $this->getNamedConfig($requestedName, $config)
        );

        // Prepare pages
        $pages = $this->preparePages(
            $container,
            $pagesFromConfig
        );

        // Create navigation
        $this->containers[$requestedName] = new Navigation($pages);

        return $this->containers[$requestedName];
    }

    /**
     * Sets the name to "default" if this factory is used for a single navigation
     *
     * @param string $requestedName
     * @return string
     */
    private function normalizeRequestedName($requestedName)
    {
        if ($requestedName === Navigation::class) {
            $requestedName = 'Zend\Navigation\Default';
        }

        return $requestedName;
    }

    /**
     * Get navigation configuration, if any
     *
     * @param  ContainerInterface $container
     * @return array
     */
    private function getConfig(ContainerInterface $container)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (! $container->has('config')) {
            $this->config = [];

            return $this->config;
        }

        $config = $container->get('config');
        if (! isset($config[self::CONFIG_KEY])
            || ! is_array($config[self::CONFIG_KEY])
        ) {
            $this->config = [];

            return $this->config;
        }

        $this->config = $config[self::CONFIG_KEY];

        return $this->config;
    }

    /**
     * Extract config name from service name
     *
     * @param string $name
     * @return string
     */
    private function getConfigName($name)
    {
        return substr($name, strlen(self::SERVICE_PREFIX));
    }

    /**
     * Does the configuration have a matching named section?
     *
     * @param string             $name
     * @param array|\ArrayAccess $config
     * @return bool
     */
    private function hasNamedConfig($name, $config)
    {
        $withoutPrefix = $this->getConfigName($name);

        if (isset($config[$withoutPrefix])) {
            return true;
        }

        if (isset($config[strtolower($withoutPrefix)])) {
            return true;
        }

        return false;
    }

    /**
     * Get the matching named configuration section.
     *
     * @param string             $name
     * @param array|\ArrayAccess $config
     * @return array
     */
    private function getNamedConfig($name, $config)
    {
        $withoutPrefix = $this->getConfigName($name);

        if (isset($config[$withoutPrefix])) {
            return $config[$withoutPrefix];
        }

        if (isset($config[strtolower($withoutPrefix)])) {
            return $config[strtolower($withoutPrefix)];
        }

        return [];
    }
}
