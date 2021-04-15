<?php

declare(strict_types=1);

namespace Flipsite\Style\Rules;

final class RuleTracking extends AbstractRule
{
    /**
     * @param array<string> $args
     */
    protected function process(array $args) : void
    {
        $value ??= $this->getConfig('letterSpacing', $args[0]);
        $value ??= $this->checkCallbacks('size', $args);
        $this->setDeclaration('letter-spacing', $value);
    }
}
