<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild\Patches;

use Illuminatech\OverrideBuild\PatchContract;

/**
 * JsonMerge
 *
 * ```php
 * new JsonMerge([
 *     'dependencies' => [
 *         'foo' => '1.0.0'
 *     ],
 * ])
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class JsonMerge implements PatchContract
{
    /**
     * @var array
     */
    private $data;

    /**
     * Constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $content): string
    {
        $data = array_merge_recursive(json_decode($content, true), $this->data);

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
