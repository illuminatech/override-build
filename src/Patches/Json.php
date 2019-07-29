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
 *     'dependencies' => [
 *         'foo' => '1.0.0',
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
            $data = $this->merge($data, $this->data);
        } else {
            $data = array_merge($data, $this->data);
        }

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Merges two or more arrays into one recursively.
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     *
     * @param array ...$args array to be merged.
     * @return array the merged array.
     */
    private static function merge(...$args)
    {
        $res = array_shift($args);
        while (! empty($args)) {
            foreach (array_shift($args) as $k => $v) {
                if (is_int($k)) {
                    if (array_key_exists($k, $res)) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = static::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }
}
