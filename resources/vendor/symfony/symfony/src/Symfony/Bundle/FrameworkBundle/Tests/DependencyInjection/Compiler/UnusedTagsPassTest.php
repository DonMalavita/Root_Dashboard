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

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\UnusedTagsPass;

class UnusedTagsPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $pass = new UnusedTagsPass();

        $formatter = Self::$getMock('Symfony\Component\DependencyInjection\Compiler\LoggingFormatter');
        $formatter
            ->expects(Self::$at(0))
            ->method('format')
            ->with($pass, 'Tag "kenrel.event_subscriber" was defined on service(s) "foo", "bar", but was never used. Did you mean "kernel.event_subscriber"?')
        ;

        $compiler = Self::$getMock('Symfony\Component\DependencyInjection\Compiler\Compiler');
        $compiler->expects(Self::$once())->method('getLoggingFormatter')->will(Self::$returnValue($formatter));

        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerBuilder',
            array('findTaggedServiceIds', 'getCompiler', 'findUnusedTags', 'findTags')
        );
        $container->expects(Self::$once())->method('getCompiler')->will(Self::$returnValue($compiler));
        $container->expects(Self::$once())
            ->method('findTags')
            ->will(Self::$returnValue(array('kenrel.event_subscriber')));
        $container->expects(Self::$once())
            ->method('findUnusedTags')
            ->will(Self::$returnValue(array('kenrel.event_subscriber', 'form.type')));
        $container->expects(Self::$once())
            ->method('findTaggedServiceIds')
            ->with('kenrel.event_subscriber')
            ->will(Self::$returnValue(array(
                'foo' => array(),
                'bar' => array(),
            )));

        $pass->process($container);
    }
}
