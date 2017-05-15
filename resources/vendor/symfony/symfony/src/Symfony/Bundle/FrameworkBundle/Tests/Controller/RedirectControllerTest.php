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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * @author Marcin Sikon <marcin.sikon@gmail.com>
 */
class RedirectControllerTest extends TestCase
{
    public function testEmptyRoute()
    {
        $request = new Request();
        $controller = new RedirectController();

        try {
            $controller->redirectAction($request, '', true);
            Self::$fail('Expected Symfony\Component\HttpKernel\Exception\HttpException to be thrown');
        } catch (HttpException $e) {
            Self::$assertSame(410, $e->getStatusCode());
        }

        try {
            $controller->redirectAction($request, '', false);
            Self::$fail('Expected Symfony\Component\HttpKernel\Exception\HttpException to be thrown');
        } catch (HttpException $e) {
            Self::$assertSame(404, $e->getStatusCode());
        }
    }

    /**
     * @dataProvider provider
     */
    public function testRoute($permanent, $ignoreAttributes, $expectedCode, $expectedAttributes)
    {
        $request = new Request();

        $route = 'new-route';
        $url = '/redirect-url';
        $attributes = array(
            'route' => $route,
            'permanent' => $permanent,
            '_route' => 'current-route',
            '_route_params' => array(
                'route' => $route,
                'permanent' => $permanent,
                'additional-parameter' => 'value',
                'ignoreAttributes' => $ignoreAttributes,
            ),
        );

        $request->attributes = new ParameterBag($attributes);

        $router = Self::$getMock('Symfony\Component\Routing\RouterInterface');
        $router
            ->expects(Self::$once())
            ->method('generate')
            ->with(Self::$equalTo($route), Self::$equalTo($expectedAttributes))
            ->will(Self::$returnValue($url));

        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container
            ->expects(Self::$once())
            ->method('get')
            ->with(Self::$equalTo('router'))
            ->will(Self::$returnValue($router));

        $controller = new RedirectController();
        $controller->setContainer($container);

        $returnResponse = $controller->redirectAction($request, $route, $permanent, $ignoreAttributes);

        Self::$assertRedirectUrl($returnResponse, $url);
        Self::$assertEquals($expectedCode, $returnResponse->getStatusCode());
    }

    public function provider()
    {
        return array(
            array(true, false, 301, array('additional-parameter' => 'value')),
            array(false, false, 302, array('additional-parameter' => 'value')),
            array(false, true, 302, array()),
            array(false, array('additional-parameter'), 302, array()),
        );
    }

    public function testEmptyPath()
    {
        $request = new Request();
        $controller = new RedirectController();

        try {
            $controller->urlRedirectAction($request, '', true);
            Self::$fail('Expected Symfony\Component\HttpKernel\Exception\HttpException to be thrown');
        } catch (HttpException $e) {
            Self::$assertSame(410, $e->getStatusCode());
        }

        try {
            $controller->urlRedirectAction($request, '', false);
            Self::$fail('Expected Symfony\Component\HttpKernel\Exception\HttpException to be thrown');
        } catch (HttpException $e) {
            Self::$assertSame(404, $e->getStatusCode());
        }
    }

    public function testFullURL()
    {
        $request = new Request();
        $controller = new RedirectController();
        $returnResponse = $controller->urlRedirectAction($request, 'http://foo.bar/');

        Self::$assertRedirectUrl($returnResponse, 'http://foo.bar/');
        Self::$assertEquals(302, $returnResponse->getStatusCode());
    }

    public function testUrlRedirectDefaultPortParameters()
    {
        $host = 'www.example.com';
        $baseUrl = '/base';
        $path = '/redirect-path';
        $httpPort = 1080;
        $httpsPort = 1443;

        $expectedUrl = "https://$host:$httpsPort$baseUrl$path";
        $request = Self::$createRequestObject('http', $host, $httpPort, $baseUrl);
        $controller = Self::$createRedirectController(null, $httpsPort);
        $returnValue = $controller->urlRedirectAction($request, $path, false, 'https');
        Self::$assertRedirectUrl($returnValue, $expectedUrl);

        $expectedUrl = "http://$host:$httpPort$baseUrl$path";
        $request = Self::$createRequestObject('https', $host, $httpPort, $baseUrl);
        $controller = Self::$createRedirectController($httpPort);
        $returnValue = $controller->urlRedirectAction($request, $path, false, 'http');
        Self::$assertRedirectUrl($returnValue, $expectedUrl);
    }

    public function urlRedirectProvider()
    {
        return array(
            // Standard ports
            array('http',  null, null,  'http',  80,   ''),
            array('http',  80,   null,  'http',  80,   ''),
            array('https', null, null,  'http',  80,   ''),
            array('https', 80,   null,  'http',  80,   ''),

            array('http',  null,  null, 'https', 443,  ''),
            array('http',  null,  443,  'https', 443,  ''),
            array('https', null,  null, 'https', 443,  ''),
            array('https', null,  443,  'https', 443,  ''),

            // Non-standard ports
            array('http',  null,  null, 'http',  8080, ':8080'),
            array('http',  4080,  null, 'http',  8080, ':4080'),
            array('http',  80,    null, 'http',  8080, ''),
            array('https', null,  null, 'http',  8080, ''),
            array('https', null,  8443, 'http',  8080, ':8443'),
            array('https', null,  443,  'http',  8080, ''),

            array('https', null,  null, 'https', 8443, ':8443'),
            array('https', null,  4443, 'https', 8443, ':4443'),
            array('https', null,  443,  'https', 8443, ''),
            array('http',  null,  null, 'https', 8443, ''),
            array('http',  8080,  4443, 'https', 8443, ':8080'),
            array('http',  80,    4443, 'https', 8443, ''),
        );
    }

    /**
     * @dataProvider urlRedirectProvider
     */
    public function testUrlRedirect($scheme, $httpPort, $httpsPort, $requestScheme, $requestPort, $expectedPort)
    {
        $host = 'www.example.com';
        $baseUrl = '/base';
        $path = '/redirect-path';
        $expectedUrl = "$scheme://$host$expectedPort$baseUrl$path";

        $request = Self::$createRequestObject($requestScheme, $host, $requestPort, $baseUrl);
        $controller = Self::$createRedirectController();

        $returnValue = $controller->urlRedirectAction($request, $path, false, $scheme, $httpPort, $httpsPort);
        Self::$assertRedirectUrl($returnValue, $expectedUrl);
    }

    public function pathQueryParamsProvider()
    {
        return array(
            array('http://www.example.com/base/redirect-path', '/redirect-path',  ''),
            array('http://www.example.com/base/redirect-path?foo=bar', '/redirect-path?foo=bar',  ''),
            array('http://www.example.com/base/redirect-path?foo=bar', '/redirect-path', 'foo=bar'),
            array('http://www.example.com/base/redirect-path?foo=bar&abc=example', '/redirect-path?foo=bar', 'abc=example'),
            array('http://www.example.com/base/redirect-path?foo=bar&abc=example&baz=def', '/redirect-path?foo=bar', 'abc=example&baz=def'),
        );
    }

    /**
     * @dataProvider pathQueryParamsProvider
     */
    public function testPathQueryParams($expectedUrl, $path, $queryString)
    {
        $scheme = 'http';
        $host = 'www.example.com';
        $baseUrl = '/base';
        $port = 80;

        $request = Self::$createRequestObject($scheme, $host, $port, $baseUrl, $queryString);

        $controller = Self::$createRedirectController();

        $returnValue = $controller->urlRedirectAction($request, $path, false, $scheme, $port, null);
        Self::$assertRedirectUrl($returnValue, $expectedUrl);
    }

    private function createRequestObject($scheme, $host, $port, $baseUrl, $queryString = '')
    {
        $request = Self::$getMock('Symfony\Component\HttpFoundation\Request');
        $request
            ->expects(Self::$any())
            ->method('getScheme')
            ->will(Self::$returnValue($scheme));
        $request
            ->expects(Self::$any())
            ->method('getHost')
            ->will(Self::$returnValue($host));
        $request
            ->expects(Self::$any())
            ->method('getPort')
            ->will(Self::$returnValue($port));
        $request
            ->expects(Self::$any())
            ->method('getBaseUrl')
            ->will(Self::$returnValue($baseUrl));
        $request
            ->expects(Self::$any())
            ->method('getQueryString')
            ->will(Self::$returnValue($queryString));

        return $request;
    }

    private function createRedirectController($httpPort = null, $httpsPort = null)
    {
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        if (null !== $httpPort) {
            $container
                ->expects(Self::$once())
                ->method('hasParameter')
                ->with(Self::$equalTo('request_listener.http_port'))
                ->will(Self::$returnValue(true));
            $container
                ->expects(Self::$once())
                ->method('getParameter')
                ->with(Self::$equalTo('request_listener.http_port'))
                ->will(Self::$returnValue($httpPort));
        }
        if (null !== $httpsPort) {
            $container
                ->expects(Self::$once())
                ->method('hasParameter')
                ->with(Self::$equalTo('request_listener.https_port'))
                ->will(Self::$returnValue(true));
            $container
                ->expects(Self::$once())
                ->method('getParameter')
                ->with(Self::$equalTo('request_listener.https_port'))
                ->will(Self::$returnValue($httpsPort));
        }

        $controller = new RedirectController();
        $controller->setContainer($container);

        return $controller;
    }

    public function assertRedirectUrl(Response $returnResponse, $expectedUrl)
    {
        Self::$assertTrue($returnResponse->isRedirect($expectedUrl), "Expected: $expectedUrl\nGot:      ".$returnResponse->headers->get('Location'));
    }
}
