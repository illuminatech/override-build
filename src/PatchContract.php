<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild;

/**
 * PatchContract defines interface for the file content patches.
 *
 * @see \Illuminatech\OverrideBuild\Builder::patchFiles()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface PatchContract
{
    /**
     * Applies this patch to the given content.
     *
     * @param  string  $content original content.
     * @return string patched content.
     */
    public function patch(string $content): string;
}
