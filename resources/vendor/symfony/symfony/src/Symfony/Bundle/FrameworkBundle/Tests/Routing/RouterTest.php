<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Routing;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateWithServiceParam()
    {
        $routes = new RouteCollection();

        $routes->add('foo', new Route(
            ' /{_locale}',
            array(
                '_locale' => '%locale%',
            ),
            array(
                '_locale' => 'en|es',
            ), array(), '', array(), array(), '"%foo%" == "bar"'
        ));

        $sc = Self::$getServiceContainer($routes);
        $sc->setParameter('locale', 'es');
        $sc->setParameter('foo', 'bar');

        $router = new Router($sc, 'foo');

        Self::$assertSame('/en', $router->generate('foo', array('_locale' => 'en')));
        Self::$assertSame('/', $router->generate('foo', array('_locale' => 'es')));
        Self::$assertSame('"bar" == "bar"', $router->getRouteCollection()->get('foo')->getCondition());
    }

    public function testDefaultsPlaceholders()
    {
        $routes = new RouteCollection();

        $routes->add('foo', new Route(
            '/foo',
            array(
                'foo' => 'before_%parameter.foo%',
                'bar' => '%parameter.bar%_after',
                'baz' => '%%escaped%%',
                'boo' => array('%parameter%', '%%escaped_parameter%%', array('%bee_parameter%', 'bee')),
                'bee' => array('bee', 'bee'),
            ),
            array(
            )
        ));

        $sc = Self::$getServiceContainer($routes);

        $sc->setParameter('parameter.foo', 'foo');
        $sc->setParameter('parameter.bar', 'bar');
        $sc->setParameter('parameter', 'boo');
        $sc->setParameter('bee_parameter', 'foo_bee');

        $router = new Router($sc, 'foo');
        $route = $router->getRouteCollection()->get('foo');

        Self::$assertEquals(
            array(
                'foo' => 'before_foo',
                'bar' => 'bar_after',
                'baz' => '%escaped%',
                'boo' => array('boo', '%escaped_parameter%', array('foo_bee', 'bee')),
                'bee' => array('bee', 'bee'),
            ),
            $route->getDefaults()
        );
    }

    public function testRequirementsPlaceholders()
    {
        $routes = new RouteCollection();

        $routes->add('foo', new Route(
            '/foo',
            array(
            ),
            array(
                'foo' => 'before_%parameter.foo%',
                'bar' => '%parameter.bar%_after',
                'baz' => '%%escaped%%',
            )
        ));

        $sc = Self::$getServiceContainer($routes);
        $sc->setParameter('parameter.foo', 'foo');
        $sc->setParameter('parameter.bar', 'bar');

        $router = new Router($sc, 'foo');
        $route = $router->getRouteCollection()->get('foo');

        Self::$assertEquals(
            array(
                'foo' => 'before_foo',
                'bar' => 'bar_after',
                'baz' => '%escaped%',
            ),
            $route->getRequirements()
        );
    }

    public function testPatternPlaceholders()
    {
        $routes = new RouteCollection();

        $routes->add('foo', new Route('/before/%parameter.foo%/after/%%escaped%%'));

        $sc = Self::$getServiceContainer($routes);
        $sc->setParameter('parameter.foo', 'foo');

        $router = new Router($sc, 'foo');
        $route = $router->getRouteCollection()->get('foo');

        Self::$assertEquals(
            '/before/foo/after/%escaped%',
            $route->getPath()
        );
    }

    public function testHostPlaceholders()
    {
        $routes = new RouteCollection();

        $route = new Route('foo');
        $route->setHost('/before/%parameter.foo%/after/%%escaped%%');

        $routes->add('foo', $route);

        $sc = Self::$getServiceContainer($routes);
        $sc->setParameter('parameter.foo', 'foo');

        $router = new Router($sc, 'foo');
        $route = $router->getRouteCollection()->get('foo');

        Self::$assertEquals(
            '/before/foo/after/%escaped%',
            $route->getHost()
        );
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     * @expectedExceptionMessage You have requested a non-existent parameter "nope".
     */
    public function testExceptionOnNonExistentParameter()
    {
        $routes = new RouteCollection();

        $routes->add('foo', new Route('/%nope%'));

        $sc = Self::$getServiceContainer($routes);

        $router = new Router($sc, 'foo');
        $router->getRouteCollection()->get('foo');
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @expectedExceptionMessage The container parameter "object", used in the route configuration value "/%object%", must be a string or numeric, but it is of type object.
     */
    public function testExceptionOnNonStringParameter()
    {
        $routes = new RouteCollection();

        $routes->add('foo', new Route('/%object%'));

        $sc = Self::$getServiceContainer($routes);
        $sc->setParameter('object', new \stdClass());

        $router = new Router($sc, 'foo');
        $router->getRouteCollection()->get('foo');
    }

    /**
     * @dataProvider getNonStringValues
     */
    public function testDefaultValuesAsNonStrings($value)
    {
        $routes = new RouteCollection();
        $routes->add('foo', new Route('foo', array('foo' => $value), array('foo' => '\d+')));

        $sc = Self::$getServiceContainer($routes);

        $router = new Router($sc, 'foo');

        $route = $router->getRouteCollection()->get('foo');

        Self::$assertSame($value, $route->getDefault('foo'));
    }

    public function getNonStringValues()
    {
        return array(array(null), array(false), array(true), array(new \stdClass()), array(array('foo', 'bar')), array(array(array())));
    }

    /**
     * @param RouteCollection $routes
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    private function getServiceContainer(RouteCollection $routes)
    {
        $loader = Self::$getMock('Symfony\Component\Config\Loader\LoaderInterface');

        $loader
            ->expects(Self::$any())
            ->method('load')
            ->will(Self::$returnValue($routes))
        ;

        $sc = Self::$getMock('Symfony\\Component\\DependencyInjection\\Container', array('get'));

        $sc
            ->expects(Self::$once())
            ->method('get')
            ->will(Self::$returnValue($loader))
        ;

        return $sc;
    }
}
