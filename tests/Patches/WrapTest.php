<?php

namespace Illuminatech\OverrideBuild\Test\Patches;

use Illuminatech\OverrideBuild\Patches\Wrap;
use Illuminatech\OverrideBuild\Test\TestCase;

class WrapTest extends TestCase
{
    public function testPatch()
    {
        $patch = new Wrap(['template' => 'Begin {{INHERITED}} End']);

        $result = $patch->patch('Middle');
        $this->assertSame('Begin Middle End', $result);
    }

    public function testPatchCustomPlaceholder()
    {
        $patch = new Wrap([
            'template' => 'Begin <content> End',
            'placeholder' => '<content>',
        ]);

        $result = $patch->patch('Middle');
        $this->assertSame('Begin Middle End', $result);
    }
}
