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
 * Wrap
 *
 * Configuration example:
 *
 * ```php
 * [
 *     '__class' => Illuminatech\OverrideBuild\Patches\Wrap::class,
 *     'template' => "Line before origin\n{{INHERITED}}\nLine after origin",
 * ]
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Wrap implements PatchContract
{
    /**
     * @var string template for the wrapping.
     * It should content {@see placeholder} value.
     */
    public $template;

    /**
     * @var string placeholder, which should mark original content in {@see template}
     */
    public $placeholder = '{{INHERITED}}';

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
        return strtr($this->template, [$this->placeholder => $content]);
    }
}
