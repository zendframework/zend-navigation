<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Navigation;

use Zend\Navigation\ConfigProvider;
use Zend\Navigation\Navigation;
use Zend\Navigation\Service;
use Zend\Navigation\View;

/**
 * Tests the class Zend_Navigation_ConfigProvider
 *
 * @group Zend_Navigation
 */
class ConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    private $config = [
        'abstract_factories' => [
            Service\NavigationAbstractServiceFactory::class,
        ],
        'aliases' => [
            'navigation' => Navigation::class,
        ],
        'delegators' => [
            'ViewHelperManager' => [
                View\ViewHelperManagerDelegatorFactory::class,
            ],
        ],
        'factories' => [
            Navigation::class => Service\DefaultNavigationFactory::class,
        ],
    ];

    public function testGetDependencyConfig()
    {
        $provider = new ConfigProvider();

        $this->assertEquals($this->config, $provider->getDependencyConfig());
    }

    public function testInvoke()
    {
        $provider = new ConfigProvider();

        $this->assertEquals(['dependencies' => $this->config], $provider());
    }
}
