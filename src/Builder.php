<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild;

use Illuminate\Support\Facades\File;
use Illuminatech\ArrayFactory\Facades\Factory;
use Symfony\Component\Finder\Finder;

/**
 * Builder performs re-building of the particular external package.
 *
 * Building sequence example:
 *
 * ```php
 * $builder = new Builder();
 * // configuration goes here
 * $builder->prepareFiles();
 * $builder->overrideFiles();
 * $builder->patchFiles();
 * $builder->build();
 * $builder->cleanupFiles();
 * ```
 *
 * @see \Illuminatech\OverrideBuild\Console\OverrideBuildCommand
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Builder
{
    /**
     * @var string path to the source files for the build.
     * For example: `base_path('vendor/some/extension')`
     */
    public $srcPath;

    /**
     * @var iterable|string[] list of files/directories, which should be taken from {@see srcPath}.
     * If not set - all files will be copied.
     * For example:
     *
     * ```
     * [
     *     'resources',
     *     '.babelrc',
     *     'package.json',
     *     'webpack.mix.js',
     * ]
     * ```
     */
    public $srcFiles = [];

    /**
     * @var string path used for the build.
     * For example: `storage_path('override-build/some-extension')`
     */
    public $buildPath;

    /**
     * @var string path containing the files for the source override.
     * For example: `resource_path('override-build/some-extension')`
     */
    public $overridePath;

    /**
     * @var array|string shell command(s) which should be used to build the result.
     * This command will be executed from the {@see buildPath} directory.
     * For example:
     *
     * ```
     * [
     *     'yarn install',
     *     'yarn run prod',
     * ]
     * ```
     */
    public $buildCommand;

    /**
     * @var iterable|string[] list of files/directories, which should be removed from {@see buildPath} once build is complete.
     * For example:
     *
     * ```
     * [
     *     'node-modules',
     * ]
     * ```
     */
    public $cleanupFiles = [];

    /**
     * @var int the permission to be set for newly created directories.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0775;

    /**
     * @var array|PatchContract[][] list of file patches in format: `[filename => [Patch1, Patch2, ...]]`.
     */
    private $patches = [];

    /**
     * @return PatchContract[][] list of file patches in format: `[filename => [Patch1, Patch2, ...]]`.
     */
    public function getPatches(): array
    {
        return $this->patches;
    }

    /**
     * @see \Illuminatech\OverrideBuild\PatchContract
     *
     * @param  array|PatchContract[]  $patches in format: `[filename1 => Patch1, filename2 => [Patch1, Patch2]]`.
     * @return static self reference.
     */
    public function setPatches(array $patches): self
    {
        $newPatches = [];
        foreach ($patches as $filename => $value) {
            if ($value instanceof PatchContract) {
                $newPatches[$filename] = [$value];
                continue;
            }

            if (is_array($value)) {
                if (isset($value['__class'])) {
                    $newPatches[$filename] = [Factory::make($value)];
                    continue;
                }

                foreach ($value as $v) {
                    $newPatches[$filename][] = Factory::make($v);
                }
                continue;
            }

            $newPatches[$filename] = [Factory::make($value)];
        }

        $this->patches = $newPatches;

        return $this;
    }

    /**
     * Copies files from {@see srcPath} to {@see buildPath}.
     */
    public function prepareFiles()
    {
        if (realpath($this->buildPath) === realpath($this->srcPath)) {
            return;
        }

        if (! file_exists($this->buildPath)) {
            File::makeDirectory($this->buildPath, $this->dirMode, true);
        }

        $srcNames = [];

        if (empty($this->srcFiles)) {
            foreach (Finder::create()->ignoreVCS(true)->ignoreDotFiles(false)->in($this->srcPath)->depth(0) as $file) {
                /** @var $file \Symfony\Component\Finder\SplFileInfo */
                $srcNames[] = $file->getFilename();
            }
        } else {
            $srcNames = $this->srcFiles;
        }

        foreach ($srcNames as $name) {
            $srcName = $this->srcPath.DIRECTORY_SEPARATOR.$name;
            $dstName = $this->buildPath.DIRECTORY_SEPARATOR.$name;
            if (is_dir($srcName)) {
                File::copyDirectory($srcName, $dstName);
            } else {
                File::copy($srcName, $dstName);
            }
        }
    }

    /**
     * Copies all files from {@see overridePath} into {@see buildPath} overriding existing ones.
     */
    public function overrideFiles()
    {
        if (empty($this->overridePath)) {
            return;
        }

        foreach (Finder::create()->files()->ignoreVCS(true)->ignoreDotFiles(false)->in($this->overridePath) as $file) {
            /** @var $file \Symfony\Component\Finder\SplFileInfo */
            $relativePath = trim(substr($file->getPathname(), strlen($this->overridePath)), '/\\');
            $dstFileName = $this->buildPath.DIRECTORY_SEPARATOR.$relativePath;
            if (file_exists($dstFileName)) {
                unlink($dstFileName);
            }

            $dstDirName = dirname($dstFileName);
            if (! is_dir($dstDirName)) {
                File::makeDirectory($dstDirName, $this->dirMode, true);
            }

            File::copy($file->getPathname(), $dstFileName);
        }
    }

    /**
     * Applies patches specified via {@see setPatches()} to the files to be built.
     */
    public function patchFiles()
    {
        foreach ($this->getPatches() as $filename => $patches) {
            $filepath = $this->buildPath.DIRECTORY_SEPARATOR.$filename;
            if (! file_exists($filepath)) {
                throw new \RuntimeException("Unable to patch '{$filepath}': file does not exist.");
            }

            $content = file_get_contents($filepath);
            foreach ($patches as $patch) {
                $content = $patch->patch($content);
            }
            file_put_contents($filepath, $content);
        }
    }

    /**
     * Performs actual materials build inside {@see buildPath} according to {@see buildCommand}.
     */
    public function build()
    {
        $commands = array_merge(
            ['cd '.escapeshellarg($this->buildPath)],
            (array) $this->buildCommand
        );

        passthru('('.implode('; ', $commands).')');
    }

    /**
     * Removes files specified via {@see cleanupFiles} from {@see buildPath}.
     */
    public function cleanupFiles()
    {
        foreach ($this->cleanupFiles as $name) {
            $fileName = $this->buildPath.DIRECTORY_SEPARATOR.$name;
            if (is_dir($fileName)) {
                File::deleteDirectory($fileName);
            } else {
                unlink($fileName);
            }
        }
    }

    /**
     * @return bool whether current build at {@see buildPath} is actual or outdated.
     */
    public function isBuildActual(): bool
    {
        if (! file_exists($this->buildPath)) {
            return false;
        }

        $srcModificationTime = $this->findDirectoryModificationTime($this->srcPath);
        if (! empty($this->overridePath)) {
            $srcModificationTime = max($srcModificationTime, $this->findDirectoryModificationTime($this->overridePath));
        }

        $buildModificationTime = $this->findDirectoryModificationTime($this->buildPath);

        return $buildModificationTime > $srcModificationTime;
    }

    /**
     * Finds the last modification time of the directory.
     *
     * @param  string  $path directory to be evaluated.
     * @return int last modification timestamp.
     */
    private function findDirectoryModificationTime(string $path): int
    {
        $lastModificationTime = 0;
        foreach (Finder::create()->files()->ignoreVCS(true)->ignoreDotFiles(false)->in($path) as $file) {
            /** @var $file \Symfony\Component\Finder\SplFileInfo */
            $fileMTime = $file->getMTime();

            if ($fileMTime > $lastModificationTime) {
                $lastModificationTime = $fileMTime;
            }
        }

        return $lastModificationTime;
    }
}
