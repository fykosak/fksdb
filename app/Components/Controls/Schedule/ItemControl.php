<?php

namespace FKSDB\Components\Controls\Schedule;


use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class DetailControl
 * @package FKSDB\Components\Forms\Controls\Payment
 * @property FileTemplate $template
 */
class ItemControl extends Control {
    /**
     * @var ModelScheduleItem
     */
    private $model;
    /**
     * @var ITranslator
     */
    private $translator;

    /**
     * DetailControl constructor.
     * @param ITranslator $translator
     */
    public function __construct(ITranslator $translator) {
        parent::__construct();
        $this->translator = $translator;
    }

    /**
     * @param ModelScheduleItem $group
     */
    public function setItem(ModelScheduleItem $group) {
        $this->model = $group;
    }

    public function render() {
        $this->template->model = $this->model;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ItemControl.latte');
        $this->template->render();
    }
}
