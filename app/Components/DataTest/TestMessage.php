<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest;

use Nette\Utils\Html;

class TestMessage
{
    /** @var Html|string */
    public $text;
    public string $id;
    public string $level;
    public ?self $parent;

    /**
     * @param Html|string $text
     */
    public function __construct(string $id, $text, string $level, ?self $parent = null)
    {
        $this->id = $id;
        $this->text = $text;
        $this->level = $level;
        $this->parent = $parent;
    }

    public function toHtml(): Html
    {
        $el = Html::el()->addHtml($this->text);
        if ($this->parent) {
            $el->addHtml($this->parent->toHtml());
        }
        return $el;
    }

    public function toText(): string
    {
        $el = Html::el()->addHtml($this->text);
        if ($this->parent) {
            $el->addText(': ');
            $el->addHtml($this->parent->toText());
        }
        return $el->getText();
    }
}
