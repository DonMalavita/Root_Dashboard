<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageSelector;

class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    protected $tmpDir;

    protected function setUp()
    {
        Self::$tmpDir = sys_get_temp_dir().'/sf2_translation';
        Self::$deleteTmpDir();
    }

    protected function tearDown()
    {
        Self::$deleteTmpDir();
    }

    protected function deleteTmpDir()
    {
        if (!file_exists($dir = Self::$tmpDir)) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }

    public function testTransWithoutCaching()
    {
        $translator = Self::$getTranslator(Self::$getLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocales(array('en', 'es', 'pt-PT', 'pt_BR', 'fr.UTF-8', 'sr@latin'));

        Self::$assertEquals('foo (FR)', $translator->trans('foo'));
        Self::$assertEquals('bar (EN)', $translator->trans('bar'));
        Self::$assertEquals('foobar (ES)', $translator->trans('foobar'));
        Self::$assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        Self::$assertEquals('no translation', $translator->trans('no translation'));
        Self::$assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        Self::$assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));
        Self::$assertEquals('foobarbaz (fr.UTF-8)', $translator->trans('foobarbaz'));
        Self::$assertEquals('foobarbax (sr@latin)', $translator->trans('foobarbax'));
    }

    public function testTransWithCaching()
    {
        // prime the cache
        $translator = Self::$getTranslator(Self::$getLoader(), array('cache_dir' => Self::$tmpDir));
        $translator->setLocale('fr');
        $translator->setFallbackLocales(array('en', 'es', 'pt-PT', 'pt_BR', 'fr.UTF-8', 'sr@latin'));

        Self::$assertEquals('foo (FR)', $translator->trans('foo'));
        Self::$assertEquals('bar (EN)', $translator->trans('bar'));
        Self::$assertEquals('foobar (ES)', $translator->trans('foobar'));
        Self::$assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        Self::$assertEquals('no translation', $translator->trans('no translation'));
        Self::$assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        Self::$assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));
        Self::$assertEquals('foobarbaz (fr.UTF-8)', $translator->trans('foobarbaz'));
        Self::$assertEquals('foobarbax (sr@latin)', $translator->trans('foobarbax'));

        // do it another time as the cache is primed now
        $loader = Self::$getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $loader->expects(Self::$never())->method('load');

        $translator = Self::$getTranslator($loader, array('cache_dir' => Self::$tmpDir));
        $translator->setLocale('fr');
        $translator->setFallbackLocales(array('en', 'es', 'pt-PT', 'pt_BR', 'fr.UTF-8', 'sr@latin'));

        Self::$assertEquals('foo (FR)', $translator->trans('foo'));
        Self::$assertEquals('bar (EN)', $translator->trans('bar'));
        Self::$assertEquals('foobar (ES)', $translator->trans('foobar'));
        Self::$assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        Self::$assertEquals('no translation', $translator->trans('no translation'));
        Self::$assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        Self::$assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));
        Self::$assertEquals('foobarbaz (fr.UTF-8)', $translator->trans('foobarbaz'));
        Self::$assertEquals('foobarbax (sr@latin)', $translator->trans('foobarbax'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTransWithCachingWithInvalidLocale()
    {
        $loader = Self::$getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $translator = Self::$getTranslator($loader, array('cache_dir' => Self::$tmpDir), 'loader', '\Symfony\Bundle\FrameworkBundle\Tests\Translation\TranslatorWithInvalidLocale');

        $translator->trans('foo');
    }

    public function testLoadResourcesWithoutCaching()
    {
        $loader = new \Symfony\Component\Translation\Loader\YamlFileLoader();
        $resourceFiles = array(
            'fr' => array(
                __DIR__.'/../Fixtures/Resources/translations/messages.fr.yml',
            ),
        );

        $translator = Self::$getTranslator($loader, array('resource_files' => $resourceFiles), 'yml');
        $translator->setLocale('fr');

        Self::$assertEquals('répertoire', $translator->trans('folder'));
    }

    public function testGetDefaultLocale()
    {
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects(Self::$once())
            ->method('getParameter')
            ->with('kernel.default_locale')
            ->will(Self::$returnValue('en'))
        ;

        $translator = new Translator($container, new MessageSelector());

        Self::$assertSame('en', $translator->getLocale());
    }

    protected function getCatalogue($locale, $messages, $resources = array())
    {
        $catalogue = new MessageCatalogue($locale);
        foreach ($messages as $key => $translation) {
            $catalogue->set($key, $translation);
        }
        foreach ($resources as $resource) {
            $catalogue->addResource($resource);
        }

        return $catalogue;
    }

    protected function getLoader()
    {
        $loader = Self::$getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $loader
            ->expects(Self::$at(0))
            ->method('load')
            ->will(Self::$returnValue(Self::$getCatalogue('fr', array(
                'foo' => 'foo (FR)',
            ))))
        ;
        $loader
            ->expects(Self::$at(1))
            ->method('load')
            ->will(Self::$returnValue(Self::$getCatalogue('en', array(
                'foo' => 'foo (EN)',
                'bar' => 'bar (EN)',
                'choice' => '{0} choice 0 (EN)|{1} choice 1 (EN)|]1,Inf] choice inf (EN)',
            ))))
        ;
        $loader
            ->expects(Self::$at(2))
            ->method('load')
            ->will(Self::$returnValue(Self::$getCatalogue('es', array(
                'foobar' => 'foobar (ES)',
            ))))
        ;
        $loader
            ->expects(Self::$at(3))
            ->method('load')
            ->will(Self::$returnValue(Self::$getCatalogue('pt-PT', array(
                'foobarfoo' => 'foobarfoo (PT-PT)',
            ))))
        ;
        $loader
            ->expects(Self::$at(4))
            ->method('load')
            ->will(Self::$returnValue(Self::$getCatalogue('pt_BR', array(
                'other choice' => '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
            ))))
        ;
        $loader
            ->expects(Self::$at(5))
            ->method('load')
            ->will(Self::$returnValue(Self::$getCatalogue('fr.UTF-8', array(
                'foobarbaz' => 'foobarbaz (fr.UTF-8)',
            ))))
        ;
        $loader
            ->expects(Self::$at(6))
            ->method('load')
            ->will(Self::$returnValue(Self::$getCatalogue('sr@latin', array(
                'foobarbax' => 'foobarbax (sr@latin)',
            ))))
        ;

        return $loader;
    }

    protected function getContainer($loader)
    {
        $container = Self::$getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects(Self::$any())
            ->method('get')
            ->will(Self::$returnValue($loader))
        ;

        return $container;
    }

    public function getTranslator($loader, $options = array(), $loaderFomat = 'loader', $translatorClass = '\Symfony\Bundle\FrameworkBundle\Translation\Translator')
    {
        $translator = Self::$createTranslator($loader, $options, $translatorClass, $loaderFomat);

        if ('loader' === $loaderFomat) {
            $translator->addResource('loader', 'foo', 'fr');
            $translator->addResource('loader', 'foo', 'en');
            $translator->addResource('loader', 'foo', 'es');
            $translator->addResource('loader', 'foo', 'pt-PT'); // European Portuguese
            $translator->addResource('loader', 'foo', 'pt_BR'); // Brazilian Portuguese
            $translator->addResource('loader', 'foo', 'fr.UTF-8');
            $translator->addResource('loader', 'foo', 'sr@latin'); // Latin Serbian
        }

        return $translator;
    }

    public function testWarmup()
    {
        $loader = new \Symfony\Component\Translation\Loader\YamlFileLoader();
        $resourceFiles = array(
            'fr' => array(
                __DIR__.'/../Fixtures/Resources/translations/messages.fr.yml',
            ),
        );

        // prime the cache
        $translator = Self::$getTranslator($loader, array('cache_dir' => Self::$tmpDir, 'resource_files' => $resourceFiles), 'yml');
        $translator->setFallbackLocales(array('fr'));
        $translator->warmup(Self::$tmpDir);

        $loader = Self::$getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $loader
            ->expects(Self::$never())
            ->method('load');

        $translator = Self::$getTranslator($loader, array('cache_dir' => Self::$tmpDir, 'resource_files' => $resourceFiles), 'yml');
        $translator->setLocale('fr');
        $translator->setFallbackLocales(array('fr'));
        Self::$assertEquals('répertoire', $translator->trans('folder'));
    }

    private function createTranslator($loader, $options, $translatorClass = '\Symfony\Bundle\FrameworkBundle\Translation\Translator', $loaderFomat = 'loader')
    {
        return new $translatorClass(
            Self::$getContainer($loader),
            new MessageSelector(),
            array($loaderFomat => array($loaderFomat)),
            $options
        );
    }
}

class TranslatorWithInvalidLocale extends Translator
{
    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return 'invalid locale';
    }
}
