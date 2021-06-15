<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210615\Symfony\Component\Config\Exception;

/**
 * Exception class for when a circular reference is detected when importing resources.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FileLoaderImportCircularReferenceException extends \RectorPrefix20210615\Symfony\Component\Config\Exception\LoaderLoadException
{
    public function __construct(array $resources, ?int $code = 0, \Throwable $previous = null)
    {
        if (null === $code) {
            trigger_deprecation('symfony/config', '5.3', 'Passing null as $code to "%s()" is deprecated, pass 0 instead.', __METHOD__);
            $code = 0;
        }
        $message = \sprintf('Circular reference detected in "%s" ("%s" > "%s").', $this->varToString($resources[0]), \implode('" > "', $resources), $resources[0]);
        \Exception::__construct($message, $code, $previous);
    }
}
