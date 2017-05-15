<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConstraintValidatorsPass;

class AddConstraintValidatorsPassTest extends \PHPUnit_Framework_TestCase
{
    public function testThatConstraintValidatorServicesAreProcessed()
    {
        $services = array(
            'my_constraint_validator_service1' => array(0 => array('alias' => 'my_constraint_validator_alias1')),
            'my_constraint_validator_service2' => array(),
        );

        $validatorFactoryDefinition = Self::$getMock('Symfony\Component\DependencyInjection\Definition');
        $container = Self::$getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            array('findTaggedServiceIds', 'getDefinition', 'hasDefinition')
        );

        $validatorDefinition1 = Self::$getMock('Symfony\Component\DependencyInjection\Definition', array('getClass'));
        $validatorDefinition2 = Self::$getMock('Symfony\Component\DependencyInjection\Definition', array('getClass'));

        $validatorDefinition1->expects(Self::$atLeastOnce())
            ->method('getClass')
            ->willReturn('My\Fully\Qualified\Class\Named\Validator1');
        $validatorDefinition2->expects(Self::$atLeastOnce())
            ->method('getClass')
            ->willReturn('My\Fully\Qualified\Class\Named\Validator2');

        $container->expects(Self::$any())
            ->method('getDefinition')
            ->with(Self::$anything())
            ->will(Self::$returnValueMap(array(
                array('my_constraint_validator_service1', $validatorDefinition1),
                array('my_constraint_validator_service2', $validatorDefinition2),
                array('validator.validator_factory', $validatorFactoryDefinition),
            )));

        $container->expects(Self::$atLeastOnce())
            ->method('findTaggedServiceIds')
            ->will(Self::$returnValue($services));
        $container->expects(Self::$atLeastOnce())
            ->method('hasDefinition')
            ->with('validator.validator_factory')
            ->will(Self::$returnValue(true));

        $validatorFactoryDefinition->expects(Self::$once())
            ->method('replaceArgument')
            ->with(1, array(
                'My\Fully\Qualified\Class\Named\Validator1' => 'my_constraint_validator_service1',
                'my_constraint_validator_alias1' => 'my_constraint_validator_service1',
                'My\Fully\Qualified\Class\Named\Validator2' => 'my_constraint_validator_service2',
            ));

        $addConstraintValidatorsPass = new AddConstraintValidatorsPass();
        $addConstraintValidatorsPass->process($container);
    }

    public function testThatCompilerPassIsIgnoredIfThereIsNoConstraintValidatorFactoryDefinition()
    {
        $definition = Self::$getMock('Symfony\Component\DependencyInjection\Definition');
        $container = Self::$getMock(
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            array('hasDefinition', 'findTaggedServiceIds', 'getDefinition')
        );

        $container->expects(Self::$never())->method('findTaggedServiceIds');
        $container->expects(Self::$never())->method('getDefinition');
        $container->expects(Self::$atLeastOnce())
            ->method('hasDefinition')
            ->with('validator.validator_factory')
            ->will(Self::$returnValue(false));
        $definition->expects(Self::$never())->method('replaceArgument');

        $addConstraintValidatorsPass = new AddConstraintValidatorsPass();
        $addConstraintValidatorsPass->process($container);
    }
}
