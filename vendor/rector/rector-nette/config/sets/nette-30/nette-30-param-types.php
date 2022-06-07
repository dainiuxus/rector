<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\MethodName;
use Rector\Nette\Rector\ClassMethod\TranslateClassMethodToVariadicsRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(TranslateClassMethodToVariadicsRector::class);
    # scalar type hints, see https://github.com/nette/component-model/commit/f69df2ca224cad7b07f1c8835679393263ea6771
    # scalar param types https://github.com/nette/security/commit/84024f612fb3f55f5d6e3e3e28eef1ad0388fa56
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'lookup', 0, new UnionType([new StringType(), new NullType()])), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'lookup', 1, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'lookupPath', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'lookupPath', 1, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'monitor', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'unmonitor', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'attached', 0, new ObjectType('RectorPrefix20220607\\Nette\\ComponentModel\\IComponent')), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'detached', 0, new ObjectType('RectorPrefix20220607\\Nette\\ComponentModel\\IComponent')), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Component', 'link', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Container', 'getComponent', 1, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\Container', 'createComponent', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\IComponent', 'setParent', 0, new UnionType([new ObjectType('RectorPrefix20220607\\Nette\\ComponentModel\\IContainer'), new NullType()])), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\IComponent', 'setParent', 1, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\ComponentModel\\IContainer', 'getComponents', 0, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Forms\\Container', 'addSelect', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Forms\\Container', 'addSelect', 3, new IntegerType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Forms\\Container', 'addMultiSelect', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Forms\\Container', 'addMultiSelect', 3, new IntegerType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Forms\\Rendering\\DefaultFormRenderer', 'render', 1, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\RadekDostal\\NetteComponents\\DateTimePicker\\DateTimePicker', 'register', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Bridges\\SecurityDI\\SecurityExtension', MethodName::CONSTRUCT, 0, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\IUserStorage', 'setAuthenticated', 0, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\IUserStorage', 'setIdentity', 0, new UnionType([new ObjectType('RectorPrefix20220607\\Nette\\Security\\IIdentity'), new NullType()])), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\IUserStorage', 'setExpiration', 1, new IntegerType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Identity', MethodName::CONSTRUCT, 2, $iterableType), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Identity', '__set', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Identity', '&__get', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Identity', '__isset', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Passwords', 'hash', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Passwords', 'verify', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Passwords', 'verify', 1, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Passwords', 'needsRehash', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'addRole', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'hasRole', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'getRoleParents', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'removeRole', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'addResource', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'addResource', 1, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'hasResource', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'resourceInheritsFrom', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'resourceInheritsFrom', 1, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'resourceInheritsFrom', 2, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'removeResource', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'allow', 3, new CallableType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'deny', 3, new CallableType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'setRule', 0, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'setRule', 1, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\Permission', 'setRule', 5, new CallableType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\User', 'logout', 0, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\User', 'getAuthenticator', 0, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\User', 'isInRole', 0, new StringType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\User', 'getAuthorizator', 0, new BooleanType()), new AddParamTypeDeclaration('RectorPrefix20220607\\Nette\\Security\\User', 'getAuthorizator', 1, new StringType())]);
};
