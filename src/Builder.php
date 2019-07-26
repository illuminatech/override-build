<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Illuminatech\ArrayFactory\Facades\Factory;

/**
 * Builder
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
     * @var iterable|string[] list of files, which should be taken from {@see srcPath}.
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
     * @var array|PatchContract[][]
     */
    private $patches = [];

    /**
     * @return PatchContract[][] list of patches in format: `[filename => [Patch1, Patch2, ...]]`
     */
    public function getPatches(): array
    {
        return $this->patches;
    }

    /**
     * @param  array|PatchContract[]  $patches in format: `[filename1 => Patch1, filename2 => [Patch1, Patch2]]`
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

    public function prepareFiles()
    {
        if (! file_exists($this->buildPath)) {
            File::makeDirectory($this->buildPath, 0775, true);
        }

        $srcNames = [];

        if (empty($this->srcFiles)) {
            foreach (Finder::create()->ignoreVCS(true)->in($this->srcPath)->depth(0) as $file) {
                /* @var $file \Symfony\Component\Finder\SplFileInfo */
                $srcNames[] = $file->getFilename();
            }
        } else {
            foreach ($this->srcFiles as $name) {
                $srcNames[] = $this->srcPath . DIRECTORY_SEPARATOR . $name;
            }
        }

        foreach ($srcNames as $name) {
            $srcName = $this->srcPath . DIRECTORY_SEPARATOR . $name;
            $dstName = $this->buildPath . DIRECTORY_SEPARATOR . $name;
            if (is_dir($srcName)) {
                File::copyDirectory($srcName, $dstName);
            } else {
                File::copy($srcName, $dstName);
            }
        }
    }

    public function overrideFiles()
    {
        if (empty($this->overridePath)) {
            return;
        }

        foreach (Finder::create()->files()->ignoreVCS(true)->in($this->srcPath) as $file) {
            /* @var $file \Symfony\Component\Finder\SplFileInfo */
            $relativePath = trim(substr($file->getPathname(), strlen($this->srcPath)), '/\\');
            $dstFileName = $this->buildPath.DIRECTORY_SEPARATOR.$relativePath;
            if (file_exists($dstFileName)) {
                unlink($dstFileName);
            }
            File::copy($file->getPathname(), $dstFileName);
        }
    }

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

    public function build()
    {
        $commands = array_merge(
            ['cd '.escapeshellarg($this->buildPath)],
            (array) $this->buildCommand
        );

        passthru('('.implode('; ', $commands).')');
    }
}
