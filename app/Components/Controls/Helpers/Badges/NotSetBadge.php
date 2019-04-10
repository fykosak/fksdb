<?php

namespace FKSDB\Components\Controls\Helpers\Badges;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;

/**
 *
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class NotSetBadge extends Control {
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * NoRecordsControl constructor.
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator) {
        parent::__construct();
        $this->translator = $translator;
    }

    public function render() {
        $this->template->setTranslator($this->translator);
        $this->template->html = static::getHtml();
        $this->template->setFile(__DIR__ . '/NotSet.latte');
        $this->template->render();
    }

    /**
     * @return Html
     */
    public static function getHtml(): Html {
        return Html::el('span')->addAttributes(['class' => 'badge badge-warning'])->addText(_('Not set'));
    }

}
