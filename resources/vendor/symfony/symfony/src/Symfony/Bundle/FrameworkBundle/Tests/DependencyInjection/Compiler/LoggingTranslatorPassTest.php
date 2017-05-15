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

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\LoggingTranslatorPass;

class LoggingTranslatorPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $definition = Self::$getMock('Symfony\Component\DependencyInjection\Definition');
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $parameterBag = Self::$getMock('Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface');

        $container->expects(Self::$exactly(2))
            ->method('hasAlias')
            ->will(Self::$returnValue(true));

        $container->expects(Self::$once())
            ->method('getParameter')
            ->will(Self::$returnValue(true));

        $container->expects(Self::$once())
            ->method('getAlias')
            ->will(Self::$returnValue('translation.default'));

        $container->expects(Self::$exactly(3))
            ->method('getDefinition')
            ->will(Self::$returnValue($definition));

        $container->expects(Self::$once())
            ->method('hasParameter')
            ->with('translator.logging')
            ->will(Self::$returnValue(true));

        $definition->expects(Self::$once())
            ->method('getClass')
            ->will(Self::$returnValue('Symfony\Bundle\FrameworkBundle\Translation\Translator'));

        $parameterBag->expects(Self::$once())
            ->method('resolveValue')
            ->will(Self::$returnValue("Symfony\Bundle\FrameworkBundle\Translation\Translator"));

        $container->expects(Self::$once())
            ->method('getParameterBag')
            ->will(Self::$returnValue($parameterBag));

        $pass = new LoggingTranslatorPass();
        $pass->process($container);
    }

    public function testThatCompilerPassIsIgnoredIfThereIsNotLoggerDefinition()
    {
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects(Self::$once())
            ->method('hasAlias')
            ->will(Self::$returnValue(false));

        $pass = new LoggingTranslatorPass();
        $pass->process($container);
    }

    public function testThatCompilerPassIsIgnoredIfThereIsNotTranslatorDefinition()
    {
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects(Self::$at(0))
            ->method('hasAlias')
            ->will(Self::$returnValue(true));

        $container->expects(Self::$at(0))
            ->method('hasAlias')
            ->will(Self::$returnValue(false));

        $pass = new LoggingTranslatorPass();
        $pass->process($container);
    }
}
