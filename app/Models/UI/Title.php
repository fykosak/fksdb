<?php

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class Title {

    public string $title;

    public string $icon;

    public function __construct(string $title, string $icon = '') {
        $this->title = $title;
        $this->icon = $icon;
    }

    public function toHtml(): Html {
        $container = Html::el('span');
        if ($this->icon) {
            $container->addHtml(Html::el('i')->addAttributes(['class' => $this->icon . ' mr-2']));
        }
        $container->addText($this->title);
        return $container;
    }
}
