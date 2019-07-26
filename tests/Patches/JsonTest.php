<?php

namespace Illuminatech\OverrideBuild\Test\Patches;

use Illuminatech\OverrideBuild\Patches\Json;
use Illuminatech\OverrideBuild\Test\TestCase;

class JsonTest extends TestCase
{
    public function testPatch()
    {
        $sourceJson = <<<'JSON'
{
    "dependencies": {
        "foo": "1.0.0",
        "bar": "1.0.0"
    }
}
JSON;

        $patch = new Json([
            'dependencies' => [
                'new' => '2.0.0',
            ],
        ]);

        $result = $patch->patch($sourceJson);
        $this->assertStringContainsString('"new": "2.0.0"', $result);
    }

    public function testPatchNonRecursive()
    {
        $sourceJson = <<<'JSON'
{
    "dependencies": {
        "foo": "1.0.0",
        "bar": "1.0.0"
    }
}
JSON;

        $patch = new Json([
            'dependencies' => [
                'new' => '2.0.0',
            ],
        ], false);

        $result = $patch->patch($sourceJson);
        $this->assertStringContainsString('"new": "2.0.0"', $result);
        $this->assertStringNotContainsString('"foo"', $result);
        $this->assertStringNotContainsString('"bar"', $result);
    }
}
