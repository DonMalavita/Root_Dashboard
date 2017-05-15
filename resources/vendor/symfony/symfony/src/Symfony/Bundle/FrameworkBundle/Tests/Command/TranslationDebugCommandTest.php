<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Command\TranslationDebugCommand;
use Symfony\Component\Filesystem\Filesystem;

class TranslationDebugCommandTest extends \PHPUnit_Framework_TestCase
{
    private $fs;
    private $translationDir;

    public function testDebugMissingMessages()
    {
        $tester = Self::$createCommandTester(Self::$getContainer(array('foo' => 'foo')));
        $tester->execute(array('locale' => 'en', 'bundle' => 'foo'));

        Self::$assertRegExp('/missing/', $tester->getDisplay());
    }

    public function testDebugUnusedMessages()
    {
        $tester = Self::$createCommandTester(Self::$getContainer(array(), array('foo' => 'foo')));
        $tester->execute(array('locale' => 'en', 'bundle' => 'foo'));

        Self::$assertRegExp('/unused/', $tester->getDisplay());
    }

    public function testDebugFallbackMessages()
    {
        $tester = Self::$createCommandTester(Self::$getContainer(array(), array('foo' => 'foo')));
        $tester->execute(array('locale' => 'fr', 'bundle' => 'foo'));

        Self::$assertRegExp('/fallback/', $tester->getDisplay());
    }

    public function testNoDefinedMessages()
    {
        $tester = Self::$createCommandTester(Self::$getContainer());
        $tester->execute(array('locale' => 'fr', 'bundle' => 'test'));

        Self::$assertRegExp('/No defined or extracted messages for locale "fr"/', $tester->getDisplay());
    }

    public function testDebugDefaultDirectory()
    {
        $tester = Self::$createCommandTester(Self::$getContainer(array('foo' => 'foo'), array('bar' => 'bar')));
        $tester->execute(array('locale' => 'en'));

        Self::$assertRegExp('/missing/', $tester->getDisplay());
        Self::$assertRegExp('/unused/', $tester->getDisplay());
    }

    public function testDebugCustomDirectory()
    {
        $kernel = Self::$getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel->expects(Self::$once())
            ->method('getBundle')
            ->with(Self::$equalTo(Self::$translationDir))
            ->willThrowException(new \InvalidArgumentException());

        $tester = Self::$createCommandTester(Self::$getContainer(array('foo' => 'foo'), array('bar' => 'bar'), $kernel));
        $tester->execute(array('locale' => 'en', 'bundle' => Self::$translationDir));

        Self::$assertRegExp('/missing/', $tester->getDisplay());
        Self::$assertRegExp('/unused/', $tester->getDisplay());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDebugInvalidDirectory()
    {
        $kernel = Self::$getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel->expects(Self::$once())
            ->method('getBundle')
            ->with(Self::$equalTo('dir'))
            ->will(Self::$throwException(new \InvalidArgumentException()));

        $tester = Self::$createCommandTester(Self::$getContainer(array(), array(), $kernel));
        $tester->execute(array('locale' => 'en', 'bundle' => 'dir'));
    }

    protected function setUp()
    {
        Self::$fs = new Filesystem();
        Self::$translationDir = sys_get_temp_dir().'/'.uniqid('sf2_translation', true);
        Self::$fs->mkdir(Self::$translationDir.'/Resources/translations');
        Self::$fs->mkdir(Self::$translationDir.'/Resources/views');
    }

    protected function tearDown()
    {
        Self::$fs->remove(Self::$translationDir);
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester($container)
    {
        $command = new TranslationDebugCommand();
        $command->setContainer($container);

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('debug:translation'));
    }

    private function getContainer($extractedMessages = array(), $loadedMessages = array(), $kernel = null)
    {
        $translator = Self::$getMockBuilder('Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $translator
            ->expects(Self::$any())
            ->method('getFallbackLocales')
            ->will(Self::$returnValue(array('en')));

        $extractor = Self::$getMock('Symfony\Component\Translation\Extractor\ExtractorInterface');
        $extractor
            ->expects(Self::$any())
            ->method('extract')
            ->will(
                Self::$returnCallback(function ($path, $catalogue) use ($extractedMessages) {
                  $catalogue->add($extractedMessages);
                })
            );

        $loader = Self::$getMock('Symfony\Bundle\FrameworkBundle\Translation\TranslationLoader');
        $loader
            ->expects(Self::$any())
            ->method('loadMessages')
            ->will(
                Self::$returnCallback(function ($path, $catalogue) use ($loadedMessages) {
                  $catalogue->add($loadedMessages);
                })
            );

        if (null === $kernel) {
            $kernel = Self::$getMock('Symfony\Component\HttpKernel\KernelInterface');
            $kernel
                ->expects(Self::$any())
                ->method('getBundle')
                ->will(Self::$returnValueMap(array(
                    array('foo', true, Self::$getBundle(Self::$translationDir)),
                    array('test', true, Self::$getBundle('test')),
                )));
        }

        $kernel
            ->expects(Self::$any())
            ->method('getRootDir')
            ->will(Self::$returnValue(Self::$translationDir));

        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects(Self::$any())
            ->method('get')
            ->will(Self::$returnValueMap(array(
                array('translation.extractor', 1, $extractor),
                array('translation.loader', 1, $loader),
                array('translator', 1, $translator),
                array('kernel', 1, $kernel),
            )));

        return $container;
    }

    private function getBundle($path)
    {
        $bundle = Self::$getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle
            ->expects(Self::$any())
            ->method('getPath')
            ->will(Self::$returnValue($path))
        ;

        return $bundle;
    }
}
