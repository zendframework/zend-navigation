<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation\Service;

use Zend\Navigation\Navigation;
use Zend\ServiceManager\AbstractFactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * Navigation abstract service factory
 *
 * Allows configuring several navigation instances. If you have a navigation config key named "special" then you can
 * use $serviceLocator->get('Zend\Navigation\Special') to retrieve a navigation instance with this configuration.
 */
final class NavigationAbstractServiceFactory implements AbstractFactoryInterface
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
    const SERVICE_PREFIX = 'Zend\Navigation\\';

    /**
     * Normalized name prefix
     */
    const NAME_PREFIX = 'zendnavigation';

    /**
     * Navigation configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Can we create a navigation by the requested name?
     *
     * @param ContainerInterface $container
     * @param string $requestedName Name by which service was requested, must start with Zend\Navigation\
     * @return bool
     */
    public function canCreateServiceWithName(ContainerInterface $container, $requestedName)
    {
        if (0 !== strpos($requestedName, self::NAME_PREFIX)) {
            return false;
        }
        $config = $this->getConfig($container);

        return (!empty($config[$this->getConfigName($requestedName)]));
    }

    /**
     * Get navigation configuration, if any
     *
     * @param  ContainerInterface $container
     * @return array
     */
    protected function getConfig(ContainerInterface $container)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (!$container->has('Config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $container->get('Config');
        if (!isset($config[self::CONFIG_KEY])
            || !is_array($config[self::CONFIG_KEY])
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
    protected function getConfigName($name)
    {
        return substr($name, strlen(self::NAME_PREFIX));
    }
}
