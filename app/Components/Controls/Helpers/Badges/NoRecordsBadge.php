<?php

namespace FKSDB\Components\Controls\Helpers\Badges;

use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;

/**
 *
 * @package FKSDB\Components\Controls\Stalking\Helpers
 *
 */
class NoRecordsBadge extends Control {
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
        $this->template->setFile(__DIR__ . '/NoRecords.latte');
        $this->template->render();
    }
}
