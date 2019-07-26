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
 * Json allows modification of the JSON content.
 *
 * Configuration example:
 *
 * ```php
 * [
 *     '__class' => Illuminatech\OverrideBuild\Patches\Json::class,
 *     'data' => [
 *         'foo' => '1.0.0'
 *     ],
 * ]
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Json implements PatchContract
{
    /**
     * @var array data to be merged into original one.
     */
    public $data = [];

    /**
     * @var bool whether to use recursive merging.
     */
    public $recursive = true;

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
        $data = json_decode($content, true);
        if ($this->recursive) {
            $data = array_merge_recursive($data, $this->data);
        } else {
            $data = array_merge($data, $this->data);
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
