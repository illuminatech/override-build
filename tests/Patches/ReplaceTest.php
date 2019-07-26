<?php

namespace Illuminatech\OverrideBuild\Test\Patches;

use Illuminatech\OverrideBuild\Test\TestCase;
use Illuminatech\OverrideBuild\Patches\Replace;

class ReplaceTest extends TestCase
{
    public function testPatch()
    {
        $patch = new Replace(['replaces' => [
            '<foo>' => '<override-foo>',
            '<bar>' => '<override-bar>',
        ]]);

        $result = $patch->patch('<root><foo><bar></root>');
        $this->assertSame('<root><override-foo><override-bar></root>', $result);
    }
}
