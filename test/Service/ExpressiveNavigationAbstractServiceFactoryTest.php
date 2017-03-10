<?php
/**
 * @see       https://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2016-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-navigation/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Navigation\Service;

use PHPUnit\Framework\TestCase;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\ZendRouter;
use Zend\Navigation\Navigation;
use Zend\Navigation\Service\ExpressiveNavigationAbstractServiceFactory;

class ExpressiveNavigationAbstractServiceFactoryTest extends TestCase
{
    /**
     * @var ExpressiveNavigationAbstractServiceFactory
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    private $container;

    protected function setUp()
    {
        // Create factory
        $this->factory = new ExpressiveNavigationAbstractServiceFactory();

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(true);
        $prophecy->get('config')->willReturn(
            [
                'navigation' => [
                    'default' => [
                        [
                            'route' => 'home',
                        ],
                    ],
                ],
            ]
        );
        $prophecy->get(RouterInterface::class)->willReturn(
            new ZendRouter()
        );
        $this->container = $prophecy->reveal();
    }

    public function testInvokeMethodShouldReturnNavigationInstance()
    {
        $factory = $this->factory;
        $this->assertInstanceOf(
            Navigation::class,
            $factory($this->container, Navigation::class)
        );
    }

    public function testCanCreateMethodWithValidName()
    {
        $this->assertTrue(
            $this->factory->canCreate($this->container, Navigation::class)
        );
    }

    public function testCanCreateMethodWithInvalidName()
    {
        $this->assertFalse(
            $this->factory->canCreate($this->container, 'Foobar')
        );
    }

    public function testCreationWithEmptyConfigShouldReturnEmptyNavigation()
    {
        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->has('config')->willReturn(true);
        $prophecy->get('config')->willReturn([]);
        $prophecy->get(RouterInterface::class)->willReturn(
            new ZendRouter()
        );
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $result = $factory($container, Navigation::class);
        $this->assertCount(0, $result);
    }
}
