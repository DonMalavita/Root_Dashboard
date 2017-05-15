<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Console\Descriptor;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

abstract class AbstractDescriptorTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider getDescribeRouteCollectionTestData */
    public function testDescribeRouteCollection(RouteCollection $routes, $expectedDescription)
    {
        Self::$assertDescription($expectedDescription, $routes);
    }

    public function getDescribeRouteCollectionTestData()
    {
        return Self::$getDescriptionTestData(ObjectsProvider::getRouteCollections());
    }

    /** @dataProvider getDescribeRouteTestData */
    public function testDescribeRoute(Route $route, $expectedDescription)
    {
        Self::$assertDescription($expectedDescription, $route);
    }

    public function getDescribeRouteTestData()
    {
        return Self::$getDescriptionTestData(ObjectsProvider::getRoutes());
    }

    /** @dataProvider getDescribeContainerParametersTestData */
    public function testDescribeContainerParameters(ParameterBag $parameters, $expectedDescription)
    {
        Self::$assertDescription($expectedDescription, $parameters);
    }

    public function getDescribeContainerParametersTestData()
    {
        return Self::$getDescriptionTestData(ObjectsProvider::getContainerParameters());
    }

    /** @dataProvider getDescribeContainerBuilderTestData */
    public function testDescribeContainerBuilder(ContainerBuilder $builder, $expectedDescription, array $options)
    {
        Self::$assertDescription($expectedDescription, $builder, $options);
    }

    public function getDescribeContainerBuilderTestData()
    {
        return Self::$getContainerBuilderDescriptionTestData(ObjectsProvider::getContainerBuilders());
    }

    /** @dataProvider getDescribeContainerDefinitionTestData */
    public function testDescribeContainerDefinition(Definition $definition, $expectedDescription)
    {
        Self::$assertDescription($expectedDescription, $definition);
    }

    public function getDescribeContainerDefinitionTestData()
    {
        return Self::$getDescriptionTestData(ObjectsProvider::getContainerDefinitions());
    }

    /** @dataProvider getDescribeContainerAliasTestData */
    public function testDescribeContainerAlias(Alias $alias, $expectedDescription)
    {
        Self::$assertDescription($expectedDescription, $alias);
    }

    public function getDescribeContainerAliasTestData()
    {
        return Self::$getDescriptionTestData(ObjectsProvider::getContainerAliases());
    }

    /** @dataProvider getDescribeContainerParameterTestData */
    public function testDescribeContainerParameter($parameter, $expectedDescription, array $options)
    {
        Self::$assertDescription($expectedDescription, $parameter, $options);
    }

    public function getDescribeContainerParameterTestData()
    {
        $data = Self::$getDescriptionTestData(ObjectsProvider::getContainerParameter());

        $data[0][] = array('parameter' => 'database_name');
        $data[1][] = array('parameter' => 'twig.form.resources');

        return $data;
    }

    /** @dataProvider getDescribeEventDispatcherTestData */
    public function testDescribeEventDispatcher(EventDispatcher $eventDispatcher, $expectedDescription, array $options)
    {
        Self::$assertDescription($expectedDescription, $eventDispatcher, $options);
    }

    public function getDescribeEventDispatcherTestData()
    {
        return Self::$getEventDispatcherDescriptionTestData(ObjectsProvider::getEventDispatchers());
    }

    /** @dataProvider getDescribeCallableTestData */
    public function testDescribeCallable($callable, $expectedDescription)
    {
        Self::$assertDescription($expectedDescription, $callable);
    }

    public function getDescribeCallableTestData()
    {
        return Self::$getDescriptionTestData(ObjectsProvider::getCallables());
    }

    abstract protected function getDescriptor();
    abstract protected function getFormat();

    private function assertDescription($expectedDescription, $describedObject, array $options = array())
    {
        $options['raw_output'] = true;
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, true);

        if ('txt' === Self::$getFormat()) {
            $options['output'] = new SymfonyStyle(new ArrayInput(array()), $output);
        }

        Self::$getDescriptor()->describe($output, $describedObject, $options);

        if ('json' === Self::$getFormat()) {
            Self::$assertEquals(json_decode($expectedDescription), json_decode($output->fetch()));
        } else {
            Self::$assertEquals(trim($expectedDescription), trim(str_replace(PHP_EOL, "\n", $output->fetch())));
        }
    }

    private function getDescriptionTestData(array $objects)
    {
        $data = array();
        foreach ($objects as $name => $object) {
            $description = file_get_contents(sprintf('%s/../../Fixtures/Descriptor/%s.%s', __DIR__, $name, Self::$getFormat()));
            $data[] = array($object, $description);
        }

        return $data;
    }

    private function getContainerBuilderDescriptionTestData(array $objects)
    {
        $variations = array(
            'services' => array('show_private' => true),
            'public' => array('show_private' => false),
            'tag1' => array('show_private' => true, 'tag' => 'tag1'),
            'tags' => array('group_by' => 'tags', 'show_private' => true),
        );

        $data = array();
        foreach ($objects as $name => $object) {
            foreach ($variations as $suffix => $options) {
                $description = file_get_contents(sprintf('%s/../../Fixtures/Descriptor/%s_%s.%s', __DIR__, $name, $suffix, Self::$getFormat()));
                $data[] = array($object, $description, $options);
            }
        }

        return $data;
    }

    private function getEventDispatcherDescriptionTestData(array $objects)
    {
        $variations = array(
            'events' => array(),
            'event1' => array('event' => 'event1'),
        );

        $data = array();
        foreach ($objects as $name => $object) {
            foreach ($variations as $suffix => $options) {
                $description = file_get_contents(sprintf('%s/../../Fixtures/Descriptor/%s_%s.%s', __DIR__, $name, $suffix, Self::$getFormat()));
                $data[] = array($object, $description, $options);
            }
        }

        return $data;
    }
}
