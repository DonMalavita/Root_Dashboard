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

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\PropertyInfoPass;
use Symfony\Component\DependencyInjection\Reference;

class PropertyInfoPassTest extends \PHPUnit_Framework_TestCase
{
    public function testServicesAreOrderedAccordingToPriority()
    {
        $services = array(
            'n3' => array('tag' => array()),
            'n1' => array('tag' => array('priority' => 200)),
            'n2' => array('tag' => array('priority' => 100)),
        );

        $expected = array(
            new Reference('n1'),
            new Reference('n2'),
            new Reference('n3'),
        );

        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerBuilder', array('findTaggedServiceIds'));

        $container->expects(Self::$any())
            ->method('findTaggedServiceIds')
            ->will(Self::$returnValue($services));

        $propertyInfoPass = new PropertyInfoPass();

        $method = new \ReflectionMethod(
            'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\PropertyInfoPass',
            'findAndSortTaggedServices'
        );
        $method->setAccessible(true);

        $actual = $method->invoke($propertyInfoPass, 'tag', $container);

        Self::$assertEquals($expected, $actual);
    }

    public function testReturningEmptyArrayWhenNoService()
    {
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerBuilder', array('findTaggedServiceIds'));

        $container->expects(Self::$any())
            ->method('findTaggedServiceIds')
            ->will(Self::$returnValue(array()));

        $propertyInfoPass = new PropertyInfoPass();

        $method = new \ReflectionMethod(
            'Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\PropertyInfoPass',
            'findAndSortTaggedServices'
        );
        $method->setAccessible(true);

        $actual = $method->invoke($propertyInfoPass, 'tag', $container);

        Self::$assertEquals(array(), $actual);
    }
}
