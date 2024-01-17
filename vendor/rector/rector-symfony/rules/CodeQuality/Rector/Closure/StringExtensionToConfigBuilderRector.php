<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Expression;
use Rector\Exception\NotImplementedYetException;
use Rector\Naming\Naming\PropertyNaming;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\CodeQuality\NodeFactory\SymfonyClosureFactory;
use Rector\Symfony\Configs\ConfigArrayHandler\NestedConfigCallsFactory;
use Rector\Symfony\Configs\ConfigArrayHandler\SecurityAccessDecisionManagerConfigArrayHandler;
use Rector\Symfony\Configs\Enum\DoctrineConfigKey;
use Rector\Symfony\Configs\Enum\SecurityConfigKey;
use Rector\Symfony\NodeAnalyzer\SymfonyClosureExtensionMatcher;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Rector\Symfony\Utils\StringUtils;
use Rector\Symfony\ValueObject\ExtensionKeyAndConfiguration;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://symfony.com/blog/new-in-symfony-5-3-config-builder-classes
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Closure\StringExtensionToConfigBuilderRector\StringExtensionToConfigBuilderRectorTest
 */
final class StringExtensionToConfigBuilderRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyClosureExtensionMatcher
     */
    private $symfonyClosureExtensionMatcher;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\Configs\ConfigArrayHandler\NestedConfigCallsFactory
     */
    private $nestedConfigCallsFactory;
    /**
     * @readonly
     * @var \Rector\Symfony\Configs\ConfigArrayHandler\SecurityAccessDecisionManagerConfigArrayHandler
     */
    private $securityAccessDecisionManagerConfigArrayHandler;
    /**
     * @readonly
     * @var \Rector\Symfony\CodeQuality\NodeFactory\SymfonyClosureFactory
     */
    private $symfonyClosureFactory;
    /**
     * @var array<string, string>
     */
    private const EXTENSION_KEY_TO_CLASS_MAP = ['security' => 'Symfony\\Config\\SecurityConfig', 'framework' => 'Symfony\\Config\\FrameworkConfig', 'monolog' => 'Symfony\\Config\\MonologConfig', 'twig' => 'Symfony\\Config\\TwigConfig', 'doctrine' => 'Symfony\\Config\\DoctrineConfig'];
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, SymfonyClosureExtensionMatcher $symfonyClosureExtensionMatcher, PropertyNaming $propertyNaming, ValueResolver $valueResolver, NestedConfigCallsFactory $nestedConfigCallsFactory, SecurityAccessDecisionManagerConfigArrayHandler $securityAccessDecisionManagerConfigArrayHandler, SymfonyClosureFactory $symfonyClosureFactory)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->symfonyClosureExtensionMatcher = $symfonyClosureExtensionMatcher;
        $this->propertyNaming = $propertyNaming;
        $this->valueResolver = $valueResolver;
        $this->nestedConfigCallsFactory = $nestedConfigCallsFactory;
        $this->securityAccessDecisionManagerConfigArrayHandler = $securityAccessDecisionManagerConfigArrayHandler;
        $this->symfonyClosureFactory = $symfonyClosureFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add config builder classes', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
        ],
    ]);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $securityConfig): void {
    $securityConfig->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        $extensionKeyAndConfiguration = $this->symfonyClosureExtensionMatcher->match($node);
        if (!$extensionKeyAndConfiguration instanceof ExtensionKeyAndConfiguration) {
            return null;
        }
        $configClass = self::EXTENSION_KEY_TO_CLASS_MAP[$extensionKeyAndConfiguration->getKey()] ?? null;
        if ($configClass === null) {
            throw new NotImplementedYetException($extensionKeyAndConfiguration->getKey());
        }
        $configVariable = $this->createConfigVariable($configClass);
        $stmts = $this->createMethodCallStmts($extensionKeyAndConfiguration->getArray(), $configVariable);
        return $this->symfonyClosureFactory->create($configClass, $node, $stmts);
    }
    /**
     * @return array<Expression<MethodCall>>
     */
    private function createMethodCallStmts(Array_ $configurationArray, Variable $configVariable) : array
    {
        $methodCallStmts = [];
        $configurationValues = $this->valueResolver->getValue($configurationArray);
        foreach ($configurationValues as $key => $value) {
            $splitMany = \false;
            $nested = \false;
            // doctrine
            if (\in_array($key, [DoctrineConfigKey::DBAL, DoctrineConfigKey::ORM], \true)) {
                $methodCallName = $key;
                $splitMany = \true;
                $nested = \true;
            } elseif ($key === SecurityConfigKey::PROVIDERS) {
                $methodCallName = SecurityConfigKey::PROVIDER;
                $splitMany = \true;
            } elseif ($key === SecurityConfigKey::FIREWALLS) {
                $methodCallName = SecurityConfigKey::FIREWALL;
                $splitMany = \true;
            } elseif ($key === SecurityConfigKey::ACCESS_CONTROL) {
                $splitMany = \true;
                $methodCallName = 'accessControl';
            } else {
                $methodCallName = StringUtils::underscoreToCamelCase($key);
            }
            if (\in_array($key, [SecurityConfigKey::ACCESS_DECISION_MANAGER, SecurityConfigKey::ENTITY])) {
                $mainMethodName = StringUtils::underscoreToCamelCase($key);
                $accessDecisionManagerMethodCalls = $this->securityAccessDecisionManagerConfigArrayHandler->handle($configurationArray, $configVariable, $mainMethodName);
                if ($accessDecisionManagerMethodCalls !== []) {
                    $methodCallStmts = \array_merge($methodCallStmts, $accessDecisionManagerMethodCalls);
                    continue;
                }
            }
            if ($splitMany) {
                if ($nested) {
                    $currentConfigCaller = new MethodCall($configVariable, $methodCallName);
                } else {
                    $currentConfigCaller = $configVariable;
                }
                foreach ($value as $itemName => $itemConfiguration) {
                    if ($nested && \is_array($itemConfiguration)) {
                        $methodCallName = $itemName;
                    }
                    if (!\is_array($itemConfiguration)) {
                        // simple call
                        $args = $this->nodeFactory->createArgs([$itemConfiguration]);
                        $itemName = StringUtils::underscoreToCamelCase($itemName);
                        $methodCall = new MethodCall($currentConfigCaller, $itemName, $args);
                        $methodCallStmts[] = new Expression($methodCall);
                        continue;
                    }
                    $nextMethodCallExpressions = $this->nestedConfigCallsFactory->create([$itemConfiguration], $currentConfigCaller, $methodCallName);
                    $methodCallStmts = \array_merge($methodCallStmts, $nextMethodCallExpressions);
                }
            } else {
                // skip empty values
                if ($value === null) {
                    continue;
                }
                $simpleMethodName = StringUtils::underscoreToCamelCase($key);
                $args = $this->nodeFactory->createArgs([$value]);
                $methodCall = new MethodCall($configVariable, $simpleMethodName, $args);
                $methodCallStmts[] = new Expression($methodCall);
            }
        }
        return $methodCallStmts;
    }
    private function createConfigVariable(string $configClass) : Variable
    {
        $variableName = $this->propertyNaming->fqnToVariableName($configClass);
        return new Variable($variableName);
    }
}
