<?php

namespace FKSDB\Model\UI;

use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * Class Title
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Title {
    use SmartObject;

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
