<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Navigation\View;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\Navigation\View\HelperConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

/**
 * Tests the class Zend_Navigation_Page_Mvc
 *
 * @group      Zend_Navigation
 */
class HelperConfigTest extends TestCase
{
    public function testConfigureServiceManagerWithConfig()
    {
        $this->markTestIncomplete('Waiting on changes to zend-view Helper\\Navigation\\PluginManager');

        $replacedMenuClass = 'Zend\View\Helper\Navigation\Links';

        $serviceManager = new ServiceManager([
            'services' => [
                'config' => [
                    'navigation_helpers' => [
                        'invokables' => [
                            'menu' => $replacedMenuClass,
                        ],
                    ],
                    'navigation' => [
                        'file'    => __DIR__ . '/../_files/navigation.xml',
                        'default' => [
                            [
                                'label' => 'Page 1',
                                'uri'   => 'page1.html',
                            ],
                            [
                                'label' => 'MVC Page',
                                'route' => 'foo',
                                'pages' => [
                                    [
                                        'label' => 'Sub MVC Page',
                                        'route' => 'foo',
                                    ],
                                ],
                            ],
                            [
                                'label' => 'Page 3',
                                'uri'   => 'page3.html',
                            ],
                        ],
                    ],
                ],
            ],
            'factories' => [
                'Navigation' => DefaultNavigationFactory::class,
                'ViewHelperManager' => function ($services) {
                    return new HelperPluginManager($services);
                },
            ],
        ]);
        $helpers = $serviceManager->get('ViewHelperManager');
        $helpers = (new HelperConfig())->configureServiceManager($helpers);

        $menu = $helpers->get('Navigation')->findHelper('menu');
        $this->assertInstanceOf($replacedMenuClass, $menu);
    }
}
