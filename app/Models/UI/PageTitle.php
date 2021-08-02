<?php

namespace FKSDB\Models\UI;

use Nette\Utils\Html;

class PageTitle extends Title
{

    public ?string $subTitle;

    public function __construct(string $title = '', string $icon = '', ?string $subTitle = null)
    {
        parent::__construct($title, $icon);
        $this->subTitle = $subTitle;
    }

    public function toHtml(bool $includeSubHeadline = false): Html
    {
        $container = parent::toHtml();
        if ($includeSubHeadline && $this->subTitle) {
            $container->addHtml(
                Html::el('small')->addAttributes(['class' => 'ml-2 text-secondary small'])->addText($this->subTitle)
            );
        }
        return $container;
    }
}
