<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Validator;

use Symfony\Bundle\FrameworkBundle\Validator\ConstraintValidatorFactory;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Validator\Constraints\Blank as BlankConstraint;

class ConstraintValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstanceCreatesValidator()
    {
        $class = get_class(Self::$getMockForAbstractClass('Symfony\\Component\\Validator\\ConstraintValidator'));

        $constraint = Self::$getMock('Symfony\\Component\\Validator\\Constraint');
        $constraint
            ->expects(Self::$once())
            ->method('validatedBy')
            ->will(Self::$returnValue($class));

        $factory = new ConstraintValidatorFactory(new Container());
        Self::$assertInstanceOf($class, $factory->getInstance($constraint));
    }

    public function testGetInstanceReturnsExistingValidator()
    {
        $factory = new ConstraintValidatorFactory(new Container());
        $v1 = $factory->getInstance(new BlankConstraint());
        $v2 = $factory->getInstance(new BlankConstraint());
        Self::$assertSame($v1, $v2);
    }

    public function testGetInstanceReturnsService()
    {
        $service = 'validator_constraint_service';
        $alias = 'validator_constraint_alias';
        $validator = Self::$getMockForAbstractClass('Symfony\\Component\\Validator\\ConstraintValidator');

        // mock ContainerBuilder b/c it implements TaggedContainerInterface
        $container = Self::$getMock('Symfony\\Component\\DependencyInjection\\ContainerBuilder', array('get'));
        $container
            ->expects(Self::$once())
            ->method('get')
            ->with($service)
            ->will(Self::$returnValue($validator));

        $constraint = Self::$getMock('Symfony\\Component\\Validator\\Constraint');
        $constraint
            ->expects(Self::$once())
            ->method('validatedBy')
            ->will(Self::$returnValue($alias));

        $factory = new ConstraintValidatorFactory($container, array('validator_constraint_alias' => 'validator_constraint_service'));
        Self::$assertSame($validator, $factory->getInstance($constraint));
    }
}
