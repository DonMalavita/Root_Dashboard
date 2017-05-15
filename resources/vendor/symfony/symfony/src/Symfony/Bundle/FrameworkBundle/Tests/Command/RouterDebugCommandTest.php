<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Command\RouterDebugCommand;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouterDebugCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testDebugAllRoutes()
    {
        $tester = Self::$createCommandTester();
        $ret = $tester->execute(array('name' => null), array('decorated' => false));

        Self::$assertEquals(0, $ret, 'Returns 0 in case of success');
        Self::$assertContains('Name   Method   Scheme   Host   Path', $tester->getDisplay());
    }

    public function testDebugSingleRoute()
    {
        $tester = Self::$createCommandTester();
        $ret = $tester->execute(array('name' => 'foo'), array('decorated' => false));

        Self::$assertEquals(0, $ret, 'Returns 0 in case of success');
        Self::$assertContains('Route Name   | foo', $tester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDebugInvalidRoute()
    {
        Self::$createCommandTester()->execute(array('name' => 'test'));
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester()
    {
        $application = new Application();

        $command = new RouterDebugCommand();
        $command->setContainer(Self::$getContainer());
        $application->add($command);

        return new CommandTester($application->find('debug:router'));
    }

    private function getContainer()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('foo', new Route('foo'));
        $router = Self::$getMock('Symfony\Component\Routing\RouterInterface');
        $router
            ->expects(Self::$any())
            ->method('getRouteCollection')
            ->will(Self::$returnValue($routeCollection))
        ;

        $loader = Self::$getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader')
             ->disableOriginalConstructor()
             ->getMock();

        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects(Self::$once())
            ->method('has')
            ->with('router')
            ->will(Self::$returnValue(true))
        ;

        $container
            ->method('get')
            ->will(Self::$returnValueMap(array(
                array('router', 1, $router),
                array('controller_name_converter', 1, $loader),
            )));

        return $container;
    }
}
