<?php

namespace FKSDB\UI;

use Nette\Utils\Html;

/**
 * Class PageTitle
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PageTitle extends Title {

    public ?string $subTitle;

    /**
     * PageTitle constructor.
     * @param string $title
     * @param string $icon
     * @param string|null $subTitle
     */
    public function __construct(string $title = '', string $icon = '', ?string $subTitle = null) {
        parent::__construct($title, $icon);
        $this->subTitle = $subTitle;
    }

    public function toHtml(): Html {
        $container = parent::toHtml();
        if ($this->subTitle) {
            $container->addHtml(Html::el('small')->addAttributes(['class' => 'ml-2 text-secondary small'])->addText($this->subTitle));
        }
        return $container;
    }
}
