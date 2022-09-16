<?php

declare(strict_types = 1);

namespace Rulezilla\Rules\Php\Phpstan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class ClassMustBeDefinedAsFinal implements Rule
{

    public function getNodeType(): string
    {
        return Class_::class;
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     * @return array<string>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (($node->isAbstract() || $node->isFinal() || $node->isAnonymous()) && $scope->getNamespace() !== null) {
            return [];
        }

       return ['Class must be defined as final.'];
    }

}