<?php

declare(strict_types=1);

namespace Flipsite\Components;

final class Script extends AbstractElement
{
    protected string $type = 'script';

    private array $code = [];

    public function addCode(string $code) : void
    {
        $this->code[] = trim($code);
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : string
    {
        $spaces = str_repeat(' ', $indentation * $level);
        $html   = $spaces.'<'.$this->type.$this->renderAttributes().'>'."\n";
        foreach ($this->code as $code) {
            $lines           = explode("\n", $code);
            $spacesNextLevel = str_repeat(' ', $indentation * ($level + 1));
            foreach ($lines as $line) {
                $html .= $spacesNextLevel.$line."\n";
            }
        }
        return $html .= $spaces.'</script>'."\n";
    }
}
