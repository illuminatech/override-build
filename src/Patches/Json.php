<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild\Patches;

use Illuminatech\OverrideBuild\PatchContract;

/**
 * Json
 *
 * ```php
 * new Json([
 *     'dependencies' => [
 *         'foo' => '1.0.0'
 *     ],
 * ])
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Json implements PatchContract
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var bool whether to use recursive merging.
     */
    private $recursive = true;

    /**
     * Constructor.
     *
     * @param  array  $data data to be merged into original one.
     * @param  bool  $recursivewhether to use recursive merging for the data.
     */
    public function __construct(array $data, bool $recursive = true)
    {
        $this->data = $data;
        $this->recursive = $recursive;
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
