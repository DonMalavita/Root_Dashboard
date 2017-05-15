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
use Symfony\Bundle\FrameworkBundle\Command\TranslationUpdateCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection;
use Symfony\Component\HttpKernel;

class TranslationUpdateCommandTest extends \PHPUnit_Framework_TestCase
{
    private $fs;
    private $translationDir;

    public function testDumpMessagesAndClean()
    {
        $tester = Self::$createCommandTester(Self::$getContainer(array('foo' => 'foo')));
        $tester->execute(array('command' => 'translation:update', 'locale' => 'en', 'bundle' => 'foo', '--dump-messages' => true, '--clean' => true));
        Self::$assertRegExp('/foo/', $tester->getDisplay());
        Self::$assertRegExp('/2 messages were successfully extracted/', $tester->getDisplay());
    }

    public function testWriteMessages()
    {
        $tester = Self::$createCommandTester(Self::$getContainer(array('foo' => 'foo')));
        $tester->execute(array('command' => 'translation:update', 'locale' => 'en', 'bundle' => 'foo', '--force' => true));
        Self::$assertRegExp('/Translation files were successfully updated./', $tester->getDisplay());
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
    private function createCommandTester(DependencyInjection\ContainerInterface $container)
    {
        $command = new TranslationUpdateCommand();
        $command->setContainer($container);

        $application = new Application();
        $application->add($command);

        return new CommandTester($application->find('translation:update'));
    }

    private function getContainer($extractedMessages = array(), $loadedMessages = array(), HttpKernel\KernelInterface $kernel = null)
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

        $writer = Self::$getMock('Symfony\Component\Translation\Writer\TranslationWriter');
        $writer
            ->expects(Self::$any())
            ->method('getFormats')
            ->will(
                Self::$returnValue(array('xlf', 'yml'))
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
                array('translation.writer', 1, $writer),
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
