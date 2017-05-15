<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SwiftmailerBundle\Tests\DependencyInjection;

use Symfony\Bundle\SwiftmailerBundle\Tests\TestCase;
use Symfony\Bundle\SwiftmailerBundle\DependencyInjection\SwiftmailerExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;

class SwiftmailerExtensionTest extends TestCase
{
    public function getConfigTypes()
    {
        return array(
            array('xml'),
            array('php'),
            array('yml')
        );
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testDefaultConfig($type)
    {
        $container = Self::$loadContainerFromFile('empty', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSendmailConfig($type)
    {
        $container = Self::$loadContainerFromFile('sendmail', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.sendmail', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testMailConfig($type)
    {
        $container = Self::$loadContainerFromFile('mail', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.mail', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testNullTransport($type)
    {
        $container = Self::$loadContainerFromFile('null', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.null', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testFull($type)
    {
        $container = Self::$loadContainerFromFile('full', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.spool', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.real', (string) $container->getAlias('swiftmailer.transport.real'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport.real'));
        Self::$assertTrue($container->has('swiftmailer.mailer.default.spool.memory'));
        Self::$assertEquals('example.org', $container->getParameter('swiftmailer.mailer.default.transport.smtp.host'));
        Self::$assertEquals('12345', $container->getParameter('swiftmailer.mailer.default.transport.smtp.port'));
        Self::$assertEquals('tls', $container->getParameter('swiftmailer.mailer.default.transport.smtp.encryption'));
        Self::$assertEquals('user', $container->getParameter('swiftmailer.mailer.default.transport.smtp.username'));
        Self::$assertEquals('pass', $container->getParameter('swiftmailer.mailer.default.transport.smtp.password'));
        Self::$assertEquals('login', $container->getParameter('swiftmailer.mailer.default.transport.smtp.auth_mode'));
        Self::$assertEquals('1000', $container->getParameter('swiftmailer.mailer.default.transport.smtp.timeout'));
        Self::$assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.default.transport.smtp.source_ip'));
        Self::$assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.redirecting')->getTags());
        Self::$assertSame('single@host.com', $container->getParameter('swiftmailer.mailer.default.single_address'));
        Self::$assertEquals(array('/foo@.*/', '/.*@bar.com$/'), $container->getParameter('swiftmailer.mailer.default.delivery_whitelist'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testManyMailers($type)
    {
        $container = Self::$loadContainerFromFile('many_mailers', $type);

        Self::$assertEquals('swiftmailer.mailer.secondary_mailer', (string) $container->getAlias('swiftmailer.mailer'));
        Self::$assertEquals('swiftmailer.mailer.secondary_mailer.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.secondary_mailer.transport.spool', (string) $container->getAlias('swiftmailer.mailer.secondary_mailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.secondary_mailer.transport.spool', (string) $container->getAlias('swiftmailer.mailer.secondary_mailer.transport'));
        Self::$assertEquals('example.org', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.host'));
        Self::$assertEquals('12345', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.port'));
        Self::$assertEquals('tls', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.encryption'));
        Self::$assertEquals('user_first', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.username'));
        Self::$assertEquals('pass_first', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.password'));
        Self::$assertEquals('login', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.auth_mode'));
        Self::$assertEquals('1000', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.timeout'));
        Self::$assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.first_mailer.transport.smtp.source_ip'));
        Self::$assertEquals('example.org', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.host'));
        Self::$assertEquals('54321', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.port'));
        Self::$assertEquals('tls', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.encryption'));
        Self::$assertEquals('user_secondary', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.username'));
        Self::$assertEquals('pass_secondary', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.password'));
        Self::$assertEquals('login', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.auth_mode'));
        Self::$assertEquals('1000', $container->getParameter('swiftmailer.mailer.secondary_mailer.transport.smtp.timeout'));
        Self::$assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.source_ip'));
        Self::$assertEquals('example.org', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.host'));
        Self::$assertEquals('12345', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.port'));
        Self::$assertEquals('tls', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.encryption'));
        Self::$assertEquals('user_third', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.username'));
        Self::$assertEquals('pass_third', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.password'));
        Self::$assertEquals('login', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.auth_mode'));
        Self::$assertEquals('1000', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.timeout'));
        Self::$assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.third_mailer.transport.smtp.source_ip'));
    }
    /**
     * @dataProvider getConfigTypes
     */
    public function testUrls($type)
    {
        $container = Self::$loadContainerFromFile('urls', $type);


        Self::$assertEquals('example.com', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.host'));
        Self::$assertEquals('12345', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.port'));
        Self::$assertEquals('tls', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.encryption'));
        Self::$assertEquals('username', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.username'));
        Self::$assertEquals('password', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.password'));
        Self::$assertEquals('login', $container->getParameter('swiftmailer.mailer.smtp_mailer.transport.smtp.auth_mode'));
    }

        /**
     * @dataProvider getConfigTypes
     */
    public function testOneMailer($type)
    {
        $container = Self::$loadContainerFromFile('one_mailer', $type);

        Self::$assertEquals('swiftmailer.mailer.main_mailer.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.main_mailer.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.main_mailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.main_mailer.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.main_mailer.transport'));
        Self::$assertEquals('example.org', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.host'));
        Self::$assertEquals('12345', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.port'));
        Self::$assertEquals('tls', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.encryption'));
        Self::$assertEquals('user', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.username'));
        Self::$assertEquals('pass', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.password'));
        Self::$assertEquals('login', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.auth_mode'));
        Self::$assertEquals('1000', $container->getParameter('swiftmailer.mailer.main_mailer.transport.smtp.timeout'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSpool($type)
    {
        $container = Self::$loadContainerFromFile('spool', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.spool', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.real', (string) $container->getAlias('swiftmailer.transport.real'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport.real'));
        Self::$assertTrue($container->has('swiftmailer.mailer.default.spool.file'), 'Default is file based spool');
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testMemorySpool($type)
    {
        $container = Self::$loadContainerFromFile('spool_memory', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.spool', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.real', (string) $container->getAlias('swiftmailer.transport.real'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport.real'));
        Self::$assertTrue($container->has('swiftmailer.mailer.default.spool.memory'), 'Memory based spool is configured');
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testServiceSpool($type)
    {
        $container = Self::$loadContainerFromFile('spool_service', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.spool', (string) $container->getAlias('swiftmailer.mailer.default.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.real', (string) $container->getAlias('swiftmailer.transport.real'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport.real'));
        Self::$assertEquals('custom_service_id', (string) $container->getAlias('swiftmailer.mailer.default.spool.service'));
        Self::$assertTrue($container->has('swiftmailer.mailer.default.spool.service'), 'Service based spool is configured');
    }

    /**
     * @dataProvider getConfigTypes
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidServiceSpool($type)
    {
        Self::$loadContainerFromFile('spool_service_invalid', $type);
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSmtpConfig($type)
    {
        $container = Self::$loadContainerFromFile('smtp', $type);

        Self::$assertEquals('swiftmailer.mailer.default.transport', (string) $container->getAlias('swiftmailer.transport'));
        Self::$assertEquals('swiftmailer.mailer.default.transport.smtp', (string) $container->getAlias('swiftmailer.mailer.default.transport'));

        Self::$assertEquals('example.org', $container->getParameter('swiftmailer.mailer.default.transport.smtp.host'));
        Self::$assertEquals('12345', $container->getParameter('swiftmailer.mailer.default.transport.smtp.port'));
        Self::$assertEquals('tls', $container->getParameter('swiftmailer.mailer.default.transport.smtp.encryption'));
        Self::$assertEquals('user', $container->getParameter('swiftmailer.mailer.default.transport.smtp.username'));
        Self::$assertEquals('pass', $container->getParameter('swiftmailer.mailer.default.transport.smtp.password'));
        Self::$assertEquals('login', $container->getParameter('swiftmailer.mailer.default.transport.smtp.auth_mode'));
        Self::$assertEquals('1000', $container->getParameter('swiftmailer.mailer.default.transport.smtp.timeout'));
        Self::$assertEquals('127.0.0.1', $container->getParameter('swiftmailer.mailer.default.transport.smtp.source_ip'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testRedirectionConfig($type)
    {
        $container = Self::$loadContainerFromFile('redirect', $type);

        Self::$assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.redirecting')->getTags());
        Self::$assertSame('single@host.com', $container->getParameter('swiftmailer.mailer.default.single_address'));
        Self::$assertEquals(array('/foo@.*/', '/.*@bar.com$/'), $container->getParameter('swiftmailer.mailer.default.delivery_whitelist'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSingleRedirectionConfig($type)
    {
        $container = Self::$loadContainerFromFile('redirect_single', $type);

        Self::$assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.redirecting')->getTags());
        Self::$assertSame('single@host.com', $container->getParameter('swiftmailer.mailer.default.single_address'));
        Self::$assertSame(array('single@host.com'), $container->getParameter('swiftmailer.mailer.default.delivery_addresses'));
        Self::$assertEquals(array('/foo@.*/'), $container->getParameter('swiftmailer.mailer.default.delivery_whitelist'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testMultiRedirectionConfig($type)
    {
        $container = Self::$loadContainerFromFile('redirect_multi', $type);

        Self::$assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.redirecting')->getTags());
        Self::$assertSame(array('first@host.com', 'second@host.com'), $container->getParameter('swiftmailer.mailer.default.delivery_addresses'));
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testAntifloodConfig($type)
    {
        $container = Self::$loadContainerFromFile('antiflood', $type);

        Self::$assertSame(array('swiftmailer.default.plugin' => array(array())), $container->getDefinition('swiftmailer.mailer.default.plugin.antiflood')->getTags());
    }

    /**
     * @dataProvider getConfigTypes
     */
    public function testSenderAddress($type)
    {
        $container = Self::$loadContainerFromFile('sender_address', $type);

        Self::$assertEquals('noreply@test.com', $container->getParameter('swiftmailer.mailer.default.sender_address'));
        Self::$assertEquals('noreply@test.com', $container->getParameter('swiftmailer.sender_address'));
        Self::$assertTrue($container->hasParameter('swiftmailer.mailer.default.sender_address'), 'The sender address is configured');
    }

    /**
     * @param  string           $file
     * @param  string           $type
     * @return ContainerBuilder
     */
    private function loadContainerFromFile($file, $type)
    {
        $container = new ContainerBuilder();

        $container->setParameter('kernel.debug', false);
        $container->setParameter('kernel.cache_dir', '/tmp');

        $container->registerExtension(new SwiftmailerExtension());
        $locator = new FileLocator(__DIR__ . '/Fixtures/config/' . $type);

        switch ($type) {
            case 'xml':
                $loader = new XmlFileLoader($container, $locator);
                break;

            case 'yml':
                $loader = new YamlFileLoader($container, $locator);
                break;

            case 'php':
                $loader = new PhpFileLoader($container, $locator);
                break;
        }

        $loader->load($file . '.' . $type);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
