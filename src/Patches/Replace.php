<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild\Patches;

use Illuminatech\OverrideBuild\PatchContract;
use Illuminatech\ArrayFactory\Facades\Factory;

/**
 * Replace
 *
 * Configuration example:
 *
 * ```php
 * [
 *     '__class' => Illuminatech\OverrideBuild\Patches\Replace::class,
 *     'replaces' => [
 *         "import Editor from 'original-editor';" => "import Editor from 'override-editor';",
 *     ],
 * ]
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
    public $replaces;

    /**
     * Constructor.
     *
     * @param  array  $config 'array factory' compatible configuration.
     */
    public function __construct(array $config = [])
    {
        Factory::configure($this, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $content): string
    {
        return strtr($content, $this->replaces);
    }
}
