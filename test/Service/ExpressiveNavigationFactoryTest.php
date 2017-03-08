<?php

namespace ZendTest\Navigation\Service;

use PHPUnit_Framework_TestCase as TestCase;
use Interop\Container\ContainerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Router\ZendRouter;
use Zend\Navigation\Exception\InvalidArgumentException;
use Zend\Navigation\Navigation;
use Zend\Navigation\Service\ExpressiveNavigationFactory;

class ExpressiveNavigationFactoryTest extends TestCase
{
    /**
     * @var ExpressiveNavigationFactory
     */
    private $factory;

    /**
     * @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    protected function setUp()
    {
        // Create factory
        $this->factory = new ExpressiveNavigationFactory();

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
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
            $factory($this->container)
        );
    }

    public function testGetPagesSetsPagesProperty()
    {
        $reflection = new \ReflectionClass($this->factory);
        $property   = $reflection->getProperty('pages');
        $property->setAccessible(true);

        $factory = $this->factory;
        $this->assertNull($property->getValue($this->factory));
        $factory($this->container);

        $this->assertTrue(is_array($property->getValue($this->factory)));
        $factory($this->container);
    }

    public function testMissingNavigationConfigShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->get('config')->willReturn([]);
        $prophecy->get(RouterInterface::class)->willReturn(
            new ZendRouter()
        );
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $factory($container);
    }

    public function testMissingDefaultConfigShouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        // Create test double for container
        /** @var ContainerInterface|\Prophecy\Prophecy\ObjectProphecy $prophecy */
        $prophecy = $this->prophesize(ContainerInterface::class);
        $prophecy->get('config')->willReturn(['navigation' => []]);
        $prophecy->get(RouterInterface::class)->willReturn(
            new ZendRouter()
        );
        $container = $prophecy->reveal();

        $factory = $this->factory;
        $factory($container);
    }
}
