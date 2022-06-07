<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use PHPStan\Type\VoidType;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
# https://github.com/doctrine/dbal/blob/master/UPGRADE.md#bc-break-changes-in-handling-string-and-binary-columns
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Platforms\\AbstractPlatform', 'getVarcharTypeDeclarationSQL', 'getStringTypeDeclarationSQL'), new MethodCallRename('RectorPrefix20220607\\Doctrine\\DBAL\\Driver\\DriverException', 'getErrorCode', 'getCode')]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('RectorPrefix20220607\\Doctrine\\DBAL\\Connection', 'ping', new VoidType())]);
    # https://github.com/doctrine/dbal/blob/master/UPGRADE.md#deprecated-abstractionresult
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['RectorPrefix20220607\\Doctrine\\DBAL\\Abstraction\\Result' => 'RectorPrefix20220607\\Doctrine\\DBAL\\Result']);
};
