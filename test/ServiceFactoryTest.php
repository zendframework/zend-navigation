<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Navigation;

use Zend\Config\Config;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\RouteStackInterface;
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
        if (! class_exists(Application::class)) {
            $this->markTestSkipped(
                'Skipping zend-mvc-related tests until that component is updated '
                . 'to be forwards-compatible with zend-servicemanager v3'
            );
        }

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

        $this->serviceManager = $serviceManager = new ServiceManager();
        $serviceManager->setService('config', $config);

        $this->router = $router = $this->prophesize(RouteStackInterface::class);
        $this->request = $request = $this->prophesize(HttpRequest::class);

        $routeMatch = new RouteMatch([
            'controller' => 'post',
            'action'     => 'view',
            'id'         => '1337',
        ]);

        $this->mvcEvent = $mvcEvent = $this->prophesize(MvcEvent::class);
        $mvcEvent->getRouteMatch()->willReturn($routeMatch);
        $mvcEvent->getRouter()->willReturn($router->reveal());
        $mvcEvent->getRequest()->willReturn($request->reveal());

        $application = $this->prophesize(Application::class);
        $application->getMvcEvent()->willReturn($mvcEvent->reveal());

        $serviceManager->setService('Application', $application->reveal());
        $serviceManager->setAllowOverride(true);
    }

    /**
     * @covers \Zend\Navigation\Service\AbstractNavigationFactory
     */
    public function testDefaultFactoryAcceptsFileString()
    {
        $this->serviceManager->setFactory('Navigation', TestAsset\FileNavigationFactory::class);
        $container = $this->serviceManager->get('Navigation');
    }

    /**
     * @covers \Zend\Navigation\Service\DefaultNavigationFactory
     */
    public function testMvcPagesGetInjectedWithComponents()
    {
        $this->serviceManager->setFactory('Navigation', DefaultNavigationFactory::class);
        $container = $this->serviceManager->get('Navigation');

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

        $this->serviceManager->setFactory('Navigation', function ($services) use ($factory) {
            return $factory($services, 'Navigation');
        });

        $container = $this->serviceManager->get('Navigation');
    }

    /**
     * @covers \Zend\Navigation\Service\ConstructedNavigationFactory
     */
    public function testMvcPagesGetInjectedWithComponentsInConstructedNavigationFactory()
    {
        $this->serviceManager->setFactory('Navigation', function ($services) {
            $argument = __DIR__ . '/_files/navigation_mvc.xml';
            $factory  = new ConstructedNavigationFactory($argument);
            return $factory($services, 'Navigation');
        });

        $container = $this->serviceManager->get('Navigation');
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
        $this->serviceManager->setFactory('Navigation', DefaultNavigationFactory::class);

        $container = $this->serviceManager->get('Navigation');
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
        $this->serviceManager->setFactory('Navigation', $factory);

        $container = $this->serviceManager->get('Navigation');
        $this->assertEquals(3, $container->count());
    }

    /**
     * @covers \Zend\Navigation\Service\ConstructedNavigationFactory
     */
    public function testConstructedFromFileString()
    {
        $argument = __DIR__ . '/_files/navigation.xml';
        $factory  = new ConstructedNavigationFactory($argument);
        $this->serviceManager->setFactory('Navigation', $factory);

        $container = $this->serviceManager->get('Navigation');
        $this->assertEquals(3, $container->count());
    }

    /**
     * @covers \Zend\Navigation\Service\ConstructedNavigationFactory
     */
    public function testConstructedFromConfig()
    {
        $argument = new Config([
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
        $this->serviceManager->setFactory('Navigation', $factory);

        $container = $this->serviceManager->get('Navigation');
        $this->assertEquals(3, $container->count());
    }

    /**
     * @covers \Zend\Navigation\Service\NavigationAbstractServiceFactory
     */
    public function testNavigationAbstractServiceFactory()
    {
        $factory = new NavigationAbstractServiceFactory();

        $this->assertTrue(
            $factory->canCreate($this->serviceManager, 'Zend\Navigation\File')
        );
        $this->assertFalse(
            $factory->canCreate($this->serviceManager, 'Zend\Navigation\Unknown')
        );

        $container = $factory(
            $this->serviceManager,
            'Zend\Navigation\File'
        );

        $this->assertInstanceOf('Zend\Navigation\Navigation', $container);
        $this->assertEquals(3, $container->count());
    }
}
