<?php

namespace Creativestyle\Composer\Patchset\Tests\Functional;

use Creativestyle\Composer\Patchset\Tests\Functional\Fixtures\ComposerRun;
use PHPUnit\Framework\TestCase;
use Creativestyle\Composer\Patchset\Tests\Functional\Fixtures\ComposerSandbox;

abstract class SandboxTestCase extends TestCase
{
    /**
     * @var ComposerSandbox
     */
    public static $sandbox;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        ComposerSandbox::$debugOutputEnabled = isset($_SERVER['argv']) && in_array('--debug', $_SERVER['argv']);

        static::$sandbox = new ComposerSandbox();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        /* Clean up projects in between tests */
        static::$sandbox->cleanupProjects();
    }

    /**
     * @return ComposerSandbox
     */
    protected function getSandbox()
    {
        return static::$sandbox;
    }

    /**
     * @param ComposerRun $composerRun
     */
    protected function assertThatComposerRunWasSuccessful(ComposerRun $composerRun)
    {
        $this->assertTrue($composerRun->getProject()->hasLockFile(), '`composer.lock` has been created');
        $this->assertTrue($composerRun->getProject()->hasVendorsInstalled(), 'vendors have been installed');
        $this->assertTrue($composerRun->isSuccessful(), sprintf('`composer %s` has been executed succesfully', $composerRun->getComposerCommand()));
    }

    /**
     * Asserts that composer operation has completed and selected files have been applied.
     *
     * Applications should be an array of {relativePathToFileToBePatched} => {patchedVerificationStringToFindInFileContents}.
     *
     * @param ComposerRun $composerRun
     * @param array $expectedApplications
     */
    protected function assertThatComposerRunHasAppliedPatches(
        ComposerRun $composerRun,
        array $expectedApplications
    ) {
        $this->assertThatComposerRunWasSuccessful($composerRun);

        foreach ($expectedApplications as $filePath => $expectedText) {
            $this->assertTrue($composerRun->getProject()->hasFile($filePath), sprintf('file %s to be patched exists', $filePath));
            $this->assertContains($expectedText, $composerRun->getProject()->getFileContents($filePath), sprintf('file `%s` has been patched - contains patched in string `%s`', $filePath, $expectedText));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
//        static::$sandbox->cleanup();
        static::$sandbox = null;
    }
}