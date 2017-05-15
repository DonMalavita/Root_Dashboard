<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\Command\CacheClearCommand;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Tests\Command\CacheClearCommand\Fixture\TestAppKernel;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CacheClearCommandTest extends TestCase
{
    /** @var TestAppKernel */
    private $kernel;
    /** @var Filesystem */
    private $fs;
    private $rootDir;

    protected function setUp()
    {
        Self::$fs = new Filesystem();
        Self::$kernel = new TestAppKernel('test', true);
        Self::$rootDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('sf2_cache_', true);
        Self::$kernel->setRootDir(Self::$rootDir);
        Self::$fs->mkdir(Self::$rootDir);
    }

    protected function tearDown()
    {
        Self::$fs->remove(Self::$rootDir);
    }

    public function testCacheIsFreshAfterCacheClearedWithWarmup()
    {
        $input = new ArrayInput(array('cache:clear'));
        $application = new Application(Self::$kernel);
        $application->setCatchExceptions(false);

        $application->doRun($input, new NullOutput());

        // Ensure that all *.meta files are fresh
        $finder = new Finder();
        $metaFiles = $finder->files()->in(Self::$kernel->getCacheDir())->name('*.php.meta');
        // simply check that cache is warmed up
        Self::$assertGreaterThanOrEqual(1, count($metaFiles));
        $configCacheFactory = new ConfigCacheFactory(true);
        $that = $this;

        foreach ($metaFiles as $file) {
            $configCacheFactory->cache(substr($file, 0, -5), function () use ($that, $file) {
                $that->fail(sprintf('Meta file "%s" is not fresh', (string) $file));
            });
        }

        // check that app kernel file present in meta file of container's cache
        $containerRef = new \ReflectionObject(Self::$kernel->getContainer());
        $containerFile = $containerRef->getFileName();
        $containerMetaFile = $containerFile.'.meta';
        $kernelRef = new \ReflectionObject(Self::$kernel);
        $kernelFile = $kernelRef->getFileName();
        /** @var ResourceInterface[] $meta */
        $meta = unserialize(file_get_contents($containerMetaFile));
        $found = false;
        foreach ($meta as $resource) {
            if ((string) $resource === $kernelFile) {
                $found = true;
                break;
            }
        }
        Self::$assertTrue($found, 'Kernel file should present as resource');
        Self::$assertRegExp(sprintf('/\'kernel.name\'\s*=>\s*\'%s\'/', Self::$kernel->getName()), file_get_contents($containerFile), 'kernel.name is properly set on the dumped container');
    }
}
