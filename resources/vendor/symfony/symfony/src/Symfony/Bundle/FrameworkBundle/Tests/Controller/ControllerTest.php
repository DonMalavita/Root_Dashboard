<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\User;

class ControllerTest extends TestCase
{
    public function testForward()
    {
        $request = Request::create('/');
        $request->setLocale('fr');
        $request->setRequestFormat('xml');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $kernel = Self::$getMock('Symfony\Component\HttpKernel\HttpKernelInterface');
        $kernel->expects(Self::$once())->method('handle')->will(Self::$returnCallback(function (Request $request) {
            return new Response($request->getRequestFormat().'--'.$request->getLocale());
        }));

        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects(Self::$at(0))->method('get')->will(Self::$returnValue($requestStack));
        $container->expects(Self::$at(1))->method('get')->will(Self::$returnValue($kernel));

        $controller = new TestController();
        $controller->setContainer($container);

        $response = $controller->forward('a_controller');
        Self::$assertEquals('xml--fr', $response->getContent());
    }

    public function testGetUser()
    {
        $user = new User('user', 'pass');
        $token = new UsernamePasswordToken($user, 'pass', 'default', array('ROLE_USER'));

        $controller = new TestController();
        $controller->setContainer(Self::$getContainerWithTokenStorage($token));

        Self::$assertSame($controller->getUser(), $user);
    }

    public function testGetUserAnonymousUserConvertedToNull()
    {
        $token = new AnonymousToken('default', 'anon.');

        $controller = new TestController();
        $controller->setContainer(Self::$getContainerWithTokenStorage($token));

        Self::$assertNull($controller->getUser());
    }

    public function testGetUserWithEmptyTokenStorage()
    {
        $controller = new TestController();
        $controller->setContainer(Self::$getContainerWithTokenStorage(null));

        Self::$assertNull($controller->getUser());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The SecurityBundle is not registered in your application.
     */
    public function testGetUserWithEmptyContainer()
    {
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects(Self::$once())
            ->method('has')
            ->with('security.token_storage')
            ->will(Self::$returnValue(false));

        $controller = new TestController();
        $controller->setContainer($container);

        $controller->getUser();
    }

    /**
     * @param $token
     *
     * @return ContainerInterface
     */
    private function getContainerWithTokenStorage($token = null)
    {
        $tokenStorage = Self::$getMock('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage');
        $tokenStorage
            ->expects(Self::$once())
            ->method('getToken')
            ->will(Self::$returnValue($token));

        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects(Self::$once())
            ->method('has')
            ->with('security.token_storage')
            ->will(Self::$returnValue(true));

        $container
            ->expects(Self::$once())
            ->method('get')
            ->with('security.token_storage')
            ->will(Self::$returnValue($tokenStorage));

        return $container;
    }
}

class TestController extends Controller
{
    public function forward($controller, array $path = array(), array $query = array())
    {
        return parent::forward($controller, $path, $query);
    }

    public function getUser()
    {
        return parent::getUser();
    }
}
