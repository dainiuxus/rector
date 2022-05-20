<?php

declare (strict_types=1);
namespace RectorPrefix20220520;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector;
use Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
return static function (\Rector\Config\RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/phpunit-exception.php');
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\MethodCall\RenameMethodRector::class, [new \Rector\Renaming\ValueObject\MethodCallRename('PHPUnit\\Framework\\TestCase', 'createMockBuilder', 'getMockBuilder')]);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\Name\RenameClassRector::class, ['PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\\Framework\\MockObject\\Stub', 'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\\Framework\\MockObject\\Stub\\ReturnStub', 'PHPUnit_Framework_MockObject_Matcher_Parameters' => 'PHPUnit\\Framework\\MockObject\\Matcher\\Parameters', 'PHPUnit_Framework_MockObject_Matcher_Invocation' => 'PHPUnit\\Framework\\MockObject\\Matcher\\Invocation', 'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\\Framework\\MockObject\\MockObject', 'PHPUnit_Framework_MockObject_Invocation_Object' => 'PHPUnit\\Framework\\MockObject\\Invocation\\ObjectInvocation']);
    $rectorConfig->ruleWithConfiguration(\Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector::class, [
        // ref. https://github.com/sebastianbergmann/phpunit/compare/5.7.9...6.0.0
        new \Rector\Renaming\ValueObject\PseudoNamespaceToNamespace('PHPUnit_', ['PHPUnit_Framework_MockObject_MockObject', 'PHPUnit_Framework_MockObject_Invocation_Object', 'PHPUnit_Framework_MockObject_Matcher_Invocation', 'PHPUnit_Framework_MockObject_Matcher_Parameters', 'PHPUnit_Framework_MockObject_Stub_Return', 'PHPUnit_Framework_MockObject_Stub']),
    ]);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector::class);
    $rectorConfig->rule(\Rector\PHPUnit\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector::class);
};
