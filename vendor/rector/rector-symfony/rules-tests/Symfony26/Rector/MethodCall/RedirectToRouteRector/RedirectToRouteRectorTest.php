<?php

declare (strict_types=1);
namespace Rector\Symfony\Tests\Symfony26\Rector\MethodCall\RedirectToRouteRector;

use Iterator;
use RectorPrefix202306\PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
final class RedirectToRouteRectorTest extends AbstractRectorTestCase
{
    public function test(string $filePath) : void
    {
        $this->doTestFile($filePath);
    }
    public static function provideData() : Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }
    public function provideConfigFilePath() : string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
