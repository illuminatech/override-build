<?php
/**
 * @link https://github.com/illuminatech
 * @copyright Copyright (c) 2019 Illuminatech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace Illuminatech\OverrideBuild\Patches;

use Illuminatech\OverrideBuild\PatchContract;

/**
 * Wrap
 *
 * ```php
 * new Wrap("Line before origin\n{{INHERITED}}\nLine after origin");
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Wrap implements PatchContract
{
    private $template;

    private $placeholder = '{{INHERITED}}';

    /**
     * Constructor.
     *
     * @param  string  $template
     * @param  string|null  $placeholder
     */
    public function __construct(string $template, ?string $placeholder = null)
    {
        $this->template = $template;

        if (isset($placeholder)) {
            $this->placeholder = $placeholder;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $content): string
    {
        return strtr($this->template, [$this->placeholder => $content]);
    }
}
