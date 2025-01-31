<?php

declare(strict_types=1);

namespace Flipsite\Components;

use Flipsite\Utils\ArrayHelper;

abstract class AbstractElement
{
    protected bool $empty       = false;
    protected bool $oneline     = false;
    protected string $type      = '';
    protected array $children   = [];
    protected string $content   = '';
    protected array $attributes = [];
    protected array $style      = [];
    protected bool $render      = true;

    public function addStyle(?array $style) : self
    {
        if (null === $style) {
            return $this;
        }
        $this->style = ArrayHelper::merge($this->style, $style);
        return $this;
    }

    public function childCount() : int
    {
        return count($this->children);
    }

    public function attributeCount() : int
    {
        return count($this->attributes) + count($this->style) ? 1 : 0;
    }

    public function getChild(string $name) : ?AbstractElement
    {
        return $this->children[$name] ?? null;
    }

    public function getChildren() : array
    {
        return $this->children;
    }

    public function setAttribute(string $attr, $value) : self
    {
        $this->attributes[$attr] = $value;
        return $this;
    }

    public function setAttributes(array $attributes) : self
    {
        foreach ($attributes as $attr => $value) {
            $this->setAttribute($attr, $value);
        }
        return $this;
    }

    public function setContent(string $content) : self
    {
        $this->content = $content;
        return $this;
    }

    public function appendContent(string $content) : self
    {
        $this->content .= $content;
        return $this;
    }

    public function prependChild(?AbstractElement $child = null, ?string $name = null) : self
    {
        if (null === $child) {
            return $this;
        }
        $children = [];
        if (null !== $name) {
            $children[$name] = $child;
        } else {
            $children[] = $child;
        }
        $this->children = array_merge($children, $this->children);
        return $this;
    }

    public function addChild(?AbstractElement $child = null, ?string $name = null) : self
    {
        if (null === $child) {
            return $this;
        }
        if (null !== $name) {
            $this->children[$name] = $child;
        } else {
            $this->children[] = $child;
        }
        return $this;
    }

    public function addChildren(array $children) : self
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
        return $this;
    }

    public function render(int $indentation = 2, int $level = 0, bool $oneline = false) : ?string
    {
        if (!$this->render) {
            return null;
        }
        $html = '';
        $i    = str_repeat(' ', $indentation * $level);
        if ('' === $this->type) {
            $html .= $i.wordwrap($this->content, 80, $i."\n");
            $html .= "\n";
            return $html;
        }
        $html = $i.'<'.$this->type.$this->renderAttributes().'>';
        if ($this->empty) {
            return $html."\n";
        }
        if (!$this->oneline && !$oneline) {
            $html .= "\n";
        }
        if (count($this->children)) {
            foreach ($this->children as $name => $child) {
                $html .= $child->render($indentation, $level + 1);
            }
        } else {
            if (!$this->oneline && !$oneline) {
                $ii = str_repeat(' ', $indentation * ($level + 1));
                $html .= $ii.wordwrap($this->content, 120, "\n".$ii)."\n";
            } else {
                $html .= $this->content;
            }
        }
        if (!$this->oneline && !$oneline) {
            $html .= $i.'</'.$this->type.'>'."\n";
        } else {
            $html .= '</'.$this->type.'>'."\n";
        }
        return $html;
    }

    protected function renderAttributes() : string
    {
        if (count($this->style)) {
            $class = $this->getClasses();
            if (mb_strlen($class)) {
                $this->setAttribute('class', $class);
            }
        }
        $html = '';
        foreach ($this->attributes as $attr => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= ' '.$attr;
                }
            } else {
                $html .= ' '.$attr.'="'.$value.'"';
            }
        }
        return $html;
    }

    private function getClasses() : string
    {
        $classes = [];
        sort($this->style);
        foreach ($this->style as $attr => $class) {
            if (is_string($class)) {
                $classes_ = explode(' ', trim($class));
                $classes  = array_merge($classes, $classes_);
            }
        }
        $classes = array_unique($classes);
        $before  = [];
        $after   = [];
        foreach ($classes as $class) {
            if (false !== mb_strpos($class, 'transform')) {
                $before[] = $class;
            } elseif (false !== mb_strpos($class, 'transition')) {
                $before[] = $class;
            } else {
                $after[] = $class;
            }
        }
        return trim(implode(' ', array_merge($before, $after)));
    }
}
