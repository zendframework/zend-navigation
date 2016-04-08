<?php
/**
 * @link      http://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation;

use Zend\View\Helper\Havigation as NavigationHelper;

class ConfigProvider
{
    /**
     * Return general-purpose zend-i18n configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'view_helpers' => $this->getViewHelperConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'abstract_factories' => [
                Service\NavigationAbstractServiceFactory::class,
            ],
            'aliases' => [
                'navigation' => Navigation::class,
            ],
            'factories' => [
                Navigation::class => Service/DefaultNavigationFactory::class,
            ],
        ];
    }

    /**
     * Return zend-navigation helper configuration.
     *
     * Obsoletes View\HelperConfig.
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return [
            'aliases' => [
                'navigation' => NavigationHelper::class,
                'Navigation' => NavigationHelper::class,
            ],
            'factories' => [
                NavigationHelper::class    => View\NavigationHelperFactory::class,
                'zendviewhelpernavigation' => View\NavigationHelperFactory::class,
            ],
        ];
    }
}
