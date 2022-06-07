<?php

declare (strict_types=1);
namespace RectorPrefix20220607;

use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;
use RectorPrefix20220607\Tracy\Dumper;
if (!\function_exists('RectorPrefix20220607\\dump_with_depth')) {
    /**
     * @param mixed $value
     */
    function dump_with_depth($value, int $depth = 2) : void
    {
        Dumper::dump($value, [Dumper::DEPTH => $depth]);
    }
}
if (!\function_exists('RectorPrefix20220607\\dn')) {
    function dn(Node $node, int $depth = 2) : void
    {
        dump_node($node, $depth);
    }
}
if (!\function_exists('RectorPrefix20220607\\dump_node')) {
    /**
     * @param \PhpParser\Node|mixed[] $node
     */
    function dump_node($node, int $depth = 2) : void
    {
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            Dumper::dump($node, [Dumper::DEPTH => $depth]);
        }
    }
}
if (!\function_exists('RectorPrefix20220607\\print_node')) {
    /**
     * @param \PhpParser\Node|mixed[] $node
     */
    function print_node($node) : void
    {
        $standard = new Standard();
        $nodes = \is_array($node) ? $node : [$node];
        foreach ($nodes as $node) {
            $printedContent = $standard->prettyPrint([$node]);
            Dumper::dump($printedContent);
        }
    }
}
