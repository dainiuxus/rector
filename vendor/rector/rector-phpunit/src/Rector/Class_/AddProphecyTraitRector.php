<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/4142
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/4141
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/4149
 *
 * @see \Rector\PHPUnit\Tests\Rector\Class_\AddProphecyTraitRector\AddProphecyTraitRectorTest
 */
final class AddProphecyTraitRector extends AbstractRector
{
    /**
     * @var \Rector\Core\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    /**
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @var string
     */
    private const PROPHECY_TRAIT = 'Prophecy\\PhpUnit\\ProphecyTrait';
    public function __construct(ClassManipulator $classManipulator, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->classManipulator = $classManipulator;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add Prophecy trait for method using $this->prophesize()', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase
{
    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

final class ExampleTest extends TestCase
{
    use ProphecyTrait;

    public function testOne(): void
    {
        $prophecy = $this->prophesize(\AnInterface::class);
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
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $traitUse = new TraitUse([new FullyQualified(self::PROPHECY_TRAIT)]);
        $node->stmts = \array_merge([$traitUse], $node->stmts);
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        $hasProphesizeMethodCall = (bool) $this->betterNodeFinder->findFirst($class, function (Node $node) : bool {
            return $this->testsNodeAnalyzer->isAssertMethodCallName($node, 'prophesize');
        });
        if (!$hasProphesizeMethodCall) {
            return \true;
        }
        return $this->classManipulator->hasTrait($class, self::PROPHECY_TRAIT);
    }
}
