<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation\View;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use Zend\View\Helper\Navigation as NavigationHelper;

/**
 * Service manager configuration for navigation view helpers
 */
class HelperConfig implements ConfigInterface
{
    /**
     * Configure the provided service manager instance with the configuration
     * in this class.
     *
     * Simply adds a factory for the navigation helper, and has it inject the helper
     * with the service locator instance.
     *
     * @param  ServiceManager $serviceManager
     * @return ServiceManager
     */
    public function configureServiceManager(ServiceManager $container)
    {
        $helperConfig = $this->toArray();
        if ($container->has('config')) {
            $helperConfig = ArrayUtils::merge($helperConfig, $this->getConfiguredHelpers($container));
        }
        return $container->withConfig($helperConfig);
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return [
            'aliases'   => [
                'navigation' => 'Navigation',
            ],
            'factories' => [
                'Navigation' => function (ContainerInterface $container) {
                    $helper = new NavigationHelper();
                    $helper->setServiceLocator($container);
                    return $helper;
                }
            ]
        ];
    }

    /**
     * Get navigation view helper service configuration.
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function getConfiguredHelpers(ContainerInterface $container)
    {
        $config = $container->get('config');
        if (! isset($config['navigation_helpers'])) {
            return [];
        }

        return (new Config($config['navigation_helpers']))->toArray();
    }
}
