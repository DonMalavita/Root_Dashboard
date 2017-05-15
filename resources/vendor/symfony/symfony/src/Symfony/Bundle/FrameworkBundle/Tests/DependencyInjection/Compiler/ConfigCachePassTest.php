<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ConfigCachePass;

class ConfigCachePassTest extends \PHPUnit_Framework_TestCase
{
    public function testThatCheckersAreProcessedInPriorityOrder()
    {
        $services = array(
            'checker_2' => array(0 => array('priority' => 100)),
            'checker_1' => array(0 => array('priority' => 200)),
            'checker_3' => array(),
        );

        $definition = Self::$getMock('Symfony\Component\DependencyInjection\Definition');
        $container = Self::$getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            array('findTaggedServiceIds', 'getDefinition', 'hasDefinition')
        );

        $container->expects(Self::$atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will(Self::$returnValue($services));
        $container->expects(Self::$atLeastOnce())
            ->method('getDefinition')
            ->with('config_cache_factory')
            ->will(Self::$returnValue($definition));

        $definition->expects(Self::$once())
            ->method('replaceArgument')
            ->with(0, array(
                    new Reference('checker_1'),
                    new Reference('checker_2'),
                    new Reference('checker_3'),
                ));

        $pass = new ConfigCachePass();
        $pass->process($container);
    }

    public function testThatCheckersCanBeMissing()
    {
        $definition = Self::$getMock('Symfony\Component\DependencyInjection\Definition');
        $container = Self::$getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            array('findTaggedServiceIds')
        );

        $container->expects(Self::$atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will(Self::$returnValue(array()));

        $pass = new ConfigCachePass();
        $pass->process($container);
    }
}
