<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Navigation;

use Zend\Config;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\Mvc\Application;
use Zend\Navigation;
use Zend\Navigation\Page\Mvc as MvcPage;
use Zend\Navigation\Service\ConstructedNavigationFactory;
use Zend\Navigation\Service\DefaultNavigationFactory;
use Zend\Navigation\Service\NavigationAbstractServiceFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Tests the class Zend\Navigation\MvcNavigationFactory
 *
 * @group      Zend_Navigation
 */
class ServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceManager;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $config = [
            'navigation' => [
                'file'    => __DIR__ . '/_files/navigation.xml',
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
        ];
        $serviceConfig = [
            'services' => [
                'config' => $config,
                'ApplicationConfig' => [
                    'modules'                 => [],
                    'module_listener_options' => [
                        'config_cache_enabled' => false,
                        'cache_dir'            => 'data/cache',
                        'module_paths'         => [],
                    ],
                ],
            ],
        ];

        $serviceManager = new ServiceManager((new ServiceManagerConfig($serviceConfig))->toArray());
        $serviceManager->get('ModuleManager')->loadModules();

        if ($serviceManager->has('ServiceListener')) {
            $serviceListener = $serviceManager->get('ServiceListener');
            $serviceManager  = $serviceListener->getConfiguredServiceManager();
        }

        // For some reason, with no modules, configuration is overwritten; may be a bug
        // in zend-modulemanager, but still investigating.
        $serviceManager = $serviceManager->withConfig(['services' => ['config' => $config]]);

        $app = $serviceManager->get('Application');
        $app->bootstrap();
        $app->getMvcEvent()->setRouteMatch(new RouteMatch([
            'controller' => 'post',
            'action'     => 'view',
            'id'         => '1337',
        ]));

        $this->serviceManager = $serviceManager;
    }

    /**
     * @covers \Zend\Navigation\Service\AbstractNavigationFactory
     */
    public function testDefaultFactoryAcceptsFileString()
    {
        $services = $this->serviceManager->withConfig(['factories' => [
            'Navigation' => TestAsset\FileNavigationFactory::class,
        ]]);
        $container = $services->get('Navigation');
    }

    /**
     * @covers \Zend\Navigation\Service\DefaultNavigationFactory
     */
    public function testMvcPagesGetInjectedWithComponents()
    {
        $services = $this->serviceManager->withConfig(['factories' => [
            'Navigation' => DefaultNavigationFactory::class,
        ]]);
        $container = $services->get('Navigation');

        $recursive = function ($that, $pages) use (&$recursive) {
            foreach ($pages as $page) {
                if ($page instanceof MvcPage) {
                    $that->assertInstanceOf('Zend\Mvc\Router\RouteStackInterface', $page->getRouter());
                    $that->assertInstanceOf('Zend\Mvc\Router\RouteMatch', $page->getRouteMatch());
                }

                $recursive($that, $page->getPages());
            }
        };
        $recursive($this, $container->getPages());
    }

    /**
     * @covers \Zend\Navigation\Service\ConstructedNavigationFactory
     */
    public function testConstructedNavigationFactoryInjectRouterAndMatcher()
    {
        $builder = $this->getMockBuilder(ConstructedNavigationFactory::class);
        $builder->setConstructorArgs([__DIR__ . '/_files/navigation_mvc.xml'])
                ->setMethods(['injectComponents']);

        $factory = $builder->getMock();

        $factory->expects($this->once())
                ->method('injectComponents')
                ->with(
                    $this->isType('array'),
                    $this->isInstanceOf('Zend\Mvc\Router\RouteMatch'),
                    $this->isInstanceOf('Zend\Mvc\Router\RouteStackInterface')
                );

        $services = $this->serviceManager->withConfig(['factories' => [
            'Navigation' => function ($services) use ($factory) {
                return $factory($services, 'Navigation');
            }
        ]]);

        $container = $services->get('Navigation');
    }

    /**
     * @covers \Zend\Navigation\Service\ConstructedNavigationFactory
     */
    public function testMvcPagesGetInjectedWithComponentsInConstructedNavigationFactory()
    {
        $services = $this->serviceManager->withConfig(['factories' => [
            'Navigation' => function ($services) {
                $argument = __DIR__ . '/_files/navigation_mvc.xml';
                $factory  = new ConstructedNavigationFactory($argument);
                return $factory($services, 'Navigation');
            },
        ]]);

        $container = $services->get('Navigation');
        $recursive = function ($that, $pages) use (&$recursive) {
            foreach ($pages as $page) {
                if ($page instanceof MvcPage) {
                    $that->assertInstanceOf('Zend\Mvc\Router\RouteStackInterface', $page->getRouter());
                    $that->assertInstanceOf('Zend\Mvc\Router\RouteMatch', $page->getRouteMatch());
                }

                $recursive($that, $page->getPages());
            }
        };
        $recursive($this, $container->getPages());
    }

    /**
     * @covers \Zend\Navigation\Service\DefaultNavigationFactory
     */
    public function testDefaultFactory()
    {
        $services = $this->serviceManager->withConfig(['factories' => [
            'Navigation' => DefaultNavigationFactory::class,
        ]]);

        $container = $services->get('Navigation');
        $this->assertEquals(3, $container->count());
    }

    /**
     * @covers \Zend\Navigation\Service\ConstructedNavigationFactory
     */
    public function testConstructedFromArray()
    {
        $argument = [
            [
                'label' => 'Page 1',
                'uri'   => 'page1.html'
            ],
            [
                'label' => 'Page 2',
                'uri'   => 'page2.html'
            ],
            [
                'label' => 'Page 3',
                'uri'   => 'page3.html'
            ]
        ];

        $factory = new ConstructedNavigationFactory($argument);
        $services = $this->serviceManager->withConfig(['factories' => [
            'Navigation' => $factory,
        ]]);

        $container = $services->get('Navigation');
        $this->assertEquals(3, $container->count());
    }

    /**
     * @covers \Zend\Navigation\Service\ConstructedNavigationFactory
     */
    public function testConstructedFromFileString()
    {
        $argument = __DIR__ . '/_files/navigation.xml';
        $factory  = new ConstructedNavigationFactory($argument);
        $services = $this->serviceManager->withConfig(['factories' => [
            'Navigation' => $factory,
        ]]);

        $container = $services->get('Navigation');
        $this->assertEquals(3, $container->count());
    }

    /**
     * @covers \Zend\Navigation\Service\ConstructedNavigationFactory
     */
    public function testConstructedFromConfig()
    {
        $argument = new Config\Config([
            [
                'label' => 'Page 1',
                'uri'   => 'page1.html'
            ],
            [
                'label' => 'Page 2',
                'uri'   => 'page2.html'
            ],
            [
                'label' => 'Page 3',
                'uri'   => 'page3.html'
            ]
        ]);

        $factory = new ConstructedNavigationFactory($argument);
        $services = $this->serviceManager->withConfig(['factories' => [
            'Navigation' => $factory,
        ]]);

        $container = $services->get('Navigation');
        $this->assertEquals(3, $container->count());
    }

    /**
     * @covers \Zend\Navigation\Service\NavigationAbstractServiceFactory
     */
    public function testNavigationAbstractServiceFactory()
    {
        $factory = new NavigationAbstractServiceFactory();

        $this->assertTrue(
            $factory->canCreateServiceWithName($this->serviceManager, 'Zend\Navigation\File')
        );
        $this->assertFalse(
            $factory->canCreateServiceWithName($this->serviceManager, 'Zend\Navigation\Unknown')
        );

        $container = $factory(
            $this->serviceManager,
            'Zend\Navigation\File'
        );

        $this->assertInstanceOf('Zend\Navigation\Navigation', $container);
        $this->assertEquals(3, $container->count());
    }
}
