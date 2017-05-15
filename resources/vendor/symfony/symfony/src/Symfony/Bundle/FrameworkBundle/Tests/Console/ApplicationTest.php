<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Console;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\ApplicationTester;

class ApplicationTest extends TestCase
{
    public function testBundleInterfaceImplementation()
    {
        $bundle = Self::$getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');

        $kernel = Self::$getKernel(array($bundle), true);

        $application = new Application($kernel);
        $application->doRun(new ArrayInput(array('list')), new NullOutput());
    }

    public function testBundleCommandsAreRegistered()
    {
        $bundle = Self::$getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects(Self::$once())->method('registerCommands');

        $kernel = Self::$getKernel(array($bundle), true);

        $application = new Application($kernel);
        $application->doRun(new ArrayInput(array('list')), new NullOutput());

        // Calling twice: registration should only be done once.
        $application->doRun(new ArrayInput(array('list')), new NullOutput());
    }

    public function testBundleCommandsAreRetrievable()
    {
        $bundle = Self::$getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects(Self::$once())->method('registerCommands');

        $kernel = Self::$getKernel(array($bundle));

        $application = new Application($kernel);
        $application->all();

        // Calling twice: registration should only be done once.
        $application->all();
    }

    public function testBundleSingleCommandIsRetrievable()
    {
        $bundle = Self::$getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects(Self::$once())->method('registerCommands');

        $kernel = Self::$getKernel(array($bundle));

        $application = new Application($kernel);

        $command = new Command('example');
        $application->add($command);

        Self::$assertSame($command, $application->get('example'));
    }

    public function testBundleCommandCanBeFound()
    {
        $bundle = Self::$getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects(Self::$once())->method('registerCommands');

        $kernel = Self::$getKernel(array($bundle));

        $application = new Application($kernel);

        $command = new Command('example');
        $application->add($command);

        Self::$assertSame($command, $application->find('example'));
    }

    public function testBundleCommandCanBeFoundByAlias()
    {
        $bundle = Self::$getMock('Symfony\Component\HttpKernel\Bundle\Bundle');
        $bundle->expects(Self::$once())->method('registerCommands');

        $kernel = Self::$getKernel(array($bundle));

        $application = new Application($kernel);

        $command = new Command('example');
        $command->setAliases(array('alias'));
        $application->add($command);

        Self::$assertSame($command, $application->find('alias'));
    }

    public function testBundleCommandsHaveRightContainer()
    {
        $command = Self::$getMockForAbstractClass('Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand', array('foo'), '', true, true, true, array('setContainer'));
        $command->setCode(function () {});
        $command->expects(Self::$exactly(2))->method('setContainer');

        $application = new Application(Self::$getKernel(array(), true));
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);
        $application->add($command);
        $tester = new ApplicationTester($application);

        // set container is called here
        $tester->run(array('command' => 'foo'));

        // as the container might have change between two runs, setContainer must called again
        $tester->run(array('command' => 'foo'));
    }

    private function getKernel(array $bundles, $useDispatcher = false)
    {
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        if ($useDispatcher) {
            $dispatcher = Self::$getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
            $dispatcher
                ->expects(Self::$atLeastOnce())
                ->method('dispatch')
            ;
            $container
                ->expects(Self::$atLeastOnce())
                ->method('get')
                ->with(Self::$equalTo('event_dispatcher'))
                ->will(Self::$returnValue($dispatcher));
        }

        $container
            ->expects(Self::$once())
            ->method('hasParameter')
            ->with(Self::$equalTo('console.command.ids'))
            ->will(Self::$returnValue(true))
        ;
        $container
            ->expects(Self::$once())
            ->method('getParameter')
            ->with(Self::$equalTo('console.command.ids'))
            ->will(Self::$returnValue(array()))
        ;

        $kernel = Self::$getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel
            ->expects(Self::$any())
            ->method('getBundles')
            ->will(Self::$returnValue($bundles))
        ;
        $kernel
            ->expects(Self::$any())
            ->method('getContainer')
            ->will(Self::$returnValue($container))
        ;

        return $kernel;
    }
}
