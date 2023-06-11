<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\UnionType;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\ParamTypeByMethodCallTypeRectorTest
 */
final class ParamTypeByMethodCallTypeRector extends AbstractScopeAwareRector
{
    /**
     * @var \Rector\TypeDeclaration\NodeAnalyzer\CallerParamMatcher
     */
    private $callerParamMatcher;
    /**
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(CallerParamMatcher $callerParamMatcher, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->callerParamMatcher = $callerParamMatcher;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change param type based on passed method call type', [new CodeSample(<<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(string $name)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go($value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeTypedService
{
    public function run(string $name)
    {
    }
}

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go(string $value)
    {
        $this->someTypedService->run($value);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            /** @var array<StaticCall|MethodCall|FuncCall> $callers */
            $callers = $this->betterNodeFinder->findInstancesOf($classMethod, [StaticCall::class, MethodCall::class, FuncCall::class]);
            foreach ($classMethod->params as $param) {
                if ($this->shouldSkipParam($param, $classMethod)) {
                    continue;
                }
                foreach ($callers as $caller) {
                    $paramType = $this->callerParamMatcher->matchCallParamType($caller, $param, $scope);
                    if ($paramType === null) {
                        continue;
                    }
                    $this->mirrorParamType($param, $paramType);
                    $hasChanged = \true;
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod) : bool
    {
        if ($classMethod->params === []) {
            return \true;
        }
        return $this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod);
    }
    /**
     * @param \PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\NullableType|\PhpParser\Node\UnionType|\PhpParser\Node\ComplexType $paramType
     */
    private function mirrorParamType(Param $decoratedParam, $paramType) : void
    {
        // mimic type
        $newParamType = $paramType;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($newParamType, static function (Node $node) {
            // original node has to removed to avoid tokens crashing from origin positions
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            return null;
        });
        $decoratedParam->type = $newParamType;
    }
    /**
     * Should skip param because one of them is conditional types?
     */
    private function isParamConditioned(Param $param, ClassMethod $classMethod) : bool
    {
        $paramName = $this->nodeNameResolver->getName($param->var);
        if ($paramName === null) {
            return \false;
        }
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, Variable::class);
        foreach ($variables as $variable) {
            if (!$this->isName($variable, $paramName)) {
                continue;
            }
            $conditional = $this->betterNodeFinder->findParentType($variable, If_::class);
            if ($conditional instanceof If_) {
                return \true;
            }
            $conditional = $this->betterNodeFinder->findParentType($variable, Ternary::class);
            if ($conditional instanceof Ternary) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipParam(Param $param, ClassMethod $classMethod) : bool
    {
        if ($this->isParamConditioned($param, $classMethod)) {
            return \true;
        }
        if ($param->variadic) {
            return \true;
        }
        // already has type, skip
        return $param->type !== null;
    }
}
