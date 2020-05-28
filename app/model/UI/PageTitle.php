<?php

namespace FKSDB\UI;

use Nette\Utils\Html;

/**
 * Class PageTitle
 * *
 */
class PageTitle extends Title {
    /** @var string|null */
    public $subTitle;

    /**
     * PageTitle constructor.
     * @param string $title
     * @param string $icon
     * @param string $subTitle
     */
    public function __construct(string $title = '', string $icon = '', string $subTitle = null) {
        parent::__construct($title, $icon);
        $this->subTitle = $subTitle;
    }

    public function toHtml(): Html {
        $container = Html::el('span');
        if ($this->icon) {
            $container->addHtml(Html::el('i')->addAttributes(['class' => $this->icon . ' mr-2']));
        }
        $container->addText($this->title);
        if ($this->subTitle) {
            $container->addHtml(Html::el('small')->addAttributes(['class' => 'ml-2 text-secondary small'])->addText($this->subTitle));
        }
        return $container;
    }
}
