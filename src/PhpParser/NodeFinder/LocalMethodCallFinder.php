<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\NodeFinder;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class LocalMethodCallFinder
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return MethodCall[]
     */
    public function match(Class_ $class, ClassMethod $classMethod) : array
    {
        $className = $this->nodeNameResolver->getName($class);
        if (!\is_string($className)) {
            return [];
        }
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $this->betterNodeFinder->findInstanceOf($class->getMethods(), MethodCall::class);
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        $matchingMethodCalls = [];
        foreach ($methodCalls as $methodCall) {
            if (!$this->nodeNameResolver->isName($methodCall->name, $classMethodName)) {
                continue;
            }
            $callerType = $this->nodeTypeResolver->getType($methodCall->var);
            if (!$callerType instanceof TypeWithClassName) {
                continue;
            }
            if ($callerType->getClassName() !== $className) {
                continue;
            }
            $matchingMethodCalls[] = $methodCall;
        }
        return $matchingMethodCalls;
    }
}
