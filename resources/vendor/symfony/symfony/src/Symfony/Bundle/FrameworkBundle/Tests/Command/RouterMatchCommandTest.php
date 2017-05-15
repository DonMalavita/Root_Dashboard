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
use Symfony\Bundle\FrameworkBundle\Command\RouterMatchCommand;
use Symfony\Bundle\FrameworkBundle\Command\RouterDebugCommand;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;

class RouterMatchCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testWithMatchPath()
    {
        $tester = Self::$createCommandTester();
        $ret = $tester->execute(array('path_info' => '/foo', 'foo'), array('decorated' => false));

        Self::$assertEquals(0, $ret, 'Returns 0 in case of success');
        Self::$assertContains('Route Name   | foo', $tester->getDisplay());
    }

    public function testWithNotMatchPath()
    {
        $tester = Self::$createCommandTester();
        $ret = $tester->execute(array('path_info' => '/test', 'foo'), array('decorated' => false));

        Self::$assertEquals(1, $ret, 'Returns 1 in case of failure');
        Self::$assertContains('None of the routes match the path "/test"', $tester->getDisplay());
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester()
    {
        $application = new Application();

        $command = new RouterMatchCommand();
        $command->setContainer(Self::$getContainer());
        $application->add($command);

        $command = new RouterDebugCommand();
        $command->setContainer(Self::$getContainer());
        $application->add($command);

        return new CommandTester($application->find('router:match'));
    }

    private function getContainer()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('foo', new Route('foo'));
        $requestContext = new RequestContext();
        $router = Self::$getMock('Symfony\Component\Routing\RouterInterface');
        $router
            ->expects(Self::$any())
            ->method('getRouteCollection')
            ->will(Self::$returnValue($routeCollection))
        ;
        $router
            ->expects(Self::$any())
            ->method('getContext')
            ->will(Self::$returnValue($requestContext))
        ;

        $loader = Self::$getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\DelegatingLoader')
             ->disableOriginalConstructor()
             ->getMock();

        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects(Self::$once())
            ->method('has')
            ->with('router')
            ->will(Self::$returnValue(true));
        $container->method('get')
            ->will(Self::$returnValueMap(array(
                array('router', 1, $router),
                array('controller_name_converter', 1, $loader),

            )));

        return $container;
    }
}
