<?php

namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\Helpers\Badges\NotSetBadge;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class AbstractValue
 * @package FKSDB\Components\Controls\DetailHelpers
 * @property FileTemplate $template
 */
abstract class AbstractValue extends Control {
    const LAYOUT_STALKING = 'stalking';
    /**
     * @var ITranslator
     */
    private $translator;
    /**
     * @var string|"stalking"|"normal"
     */
    private $mode;

    /**
     * AbstractValue constructor.
     * @param ITranslator $translator
     * @param string $mode
     */
    public function __construct(ITranslator $translator, string $mode = 'normal') {
        parent::__construct();
        $this->translator = $translator;
        $this->mode = $mode;
    }

    /**
     * @param string $title
     */
    protected function beforeRender(string $title) {
        $this->template->setTranslator($this->translator);
        $this->template->title = $title;
        $this->template->mode = $this->mode;
    }

    /**
     * @return NotSetBadge
     */
    public function createComponentNotSet(): NotSetBadge {
        return new NotSetBadge($this->translator);
    }
}
