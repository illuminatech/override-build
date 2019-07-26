<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild\Patches;

use Illuminatech\OverrideBuild\PatchContract;

/**
 * Replace
 *
 * ```php
 * new Replace([
 *     "import Editor from 'original-editor';" => "import Editor from 'override-editor';",
 * ])
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Replace implements PatchContract
{
    /**
     * @var array list of content replaces.
     */
    private $replaces;

    /**
     * Constructor.
     *
     * @param  array  $replaces list of content replaces in format: `[search => replace]`.
     */
    public function __construct(array $replaces)
    {
        $this->replaces = $replaces;
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $content): string
    {
        return strtr($content, $this->replaces);
    }
}
