<?php

declare (strict_types=1);
namespace Rector\PHPUnit\ValueObject;

final class AnnotationWithValueToAttribute
{
    /**
     * @var string
     */
    private $annotationName;
    /**
     * @var string
     */
    private $attributeClass;
    /**
     * @var array<mixed, mixed>
     */
    private $valueMap = [];
    /**
     * @param array<mixed, mixed> $valueMap
     */
    public function __construct(string $annotationName, string $attributeClass, array $valueMap = [])
    {
        $this->annotationName = $annotationName;
        $this->attributeClass = $attributeClass;
        $this->valueMap = $valueMap;
    }
    public function getAnnotationName() : string
    {
        return $this->annotationName;
    }
    public function getAttributeClass() : string
    {
        return $this->attributeClass;
    }
    /**
     * @return array<mixed, mixed>
     */
    public function getValueMap() : array
    {
        return $this->valueMap;
    }
}
