<?php

namespace FKSDB\Components\Controls\Schedule;


use FKSDB\Components\Controls\Helpers\AbstractDetailControl;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use Nette\Templating\FileTemplate;

/**
 * Class DetailControl
 * @package FKSDB\Components\Forms\Controls\Payment
 * @property FileTemplate $template
 */
class ItemControl extends AbstractDetailControl {
    /**
     * @var ModelScheduleItem
     */
    private $model;

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
