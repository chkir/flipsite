<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleFlex extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value = $this->getConfig('flex', $args[0]);
        $this->setDeclaration('flex', $value);
    }
}
