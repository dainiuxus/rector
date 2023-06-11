<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\PhpParser\Comparing\NodeComparator;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeNameResolver\NodeNameResolver;
final class ObjectToResourceReturn
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var \Rector\Core\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, NodeComparator $nodeComparator, NodeFactory $nodeFactory)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeComparator = $nodeComparator;
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param string[] $collectionObjectToResource
     */
    public function refactor(Instanceof_ $instanceof, array $collectionObjectToResource) : ?BooleanOr
    {
        if (!$instanceof->class instanceof FullyQualified) {
            return null;
        }
        $className = $instanceof->class->toString();
        foreach ($collectionObjectToResource as $singleCollectionObjectToResource) {
            if ($singleCollectionObjectToResource !== $className) {
                continue;
            }
            $binaryOp = $this->betterNodeFinder->findParentType($instanceof, BinaryOp::class);
            if ($this->hasIsResourceCheck($instanceof->expr, $binaryOp)) {
                continue;
            }
            return new BooleanOr($this->nodeFactory->createFuncCall('is_resource', [$instanceof->expr]), $instanceof);
        }
        return null;
    }
    private function hasIsResourceCheck(Expr $expr, ?BinaryOp $binaryOp) : bool
    {
        if ($binaryOp instanceof BinaryOp) {
            return (bool) $this->betterNodeFinder->findFirst($binaryOp, function (Node $subNode) use($expr) : bool {
                if (!$subNode instanceof FuncCall) {
                    return \false;
                }
                if (!$subNode->name instanceof Name) {
                    return \false;
                }
                if (!$this->nodeNameResolver->isName($subNode->name, 'is_resource')) {
                    return \false;
                }
                $args = $subNode->getArgs();
                if (!isset($args[0])) {
                    return \false;
                }
                return $this->nodeComparator->areNodesEqual($args[0], $expr);
            });
        }
        return \false;
    }
}
