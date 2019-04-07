<?php

namespace FKSDB\Components\Controls\Schedule;

use FKSDB\Components\Controls\Helpers\AbstractDetailControl;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use Nette\Templating\FileTemplate;

/**
 * Class DetailControl
 * @package FKSDB\Components\Forms\Controls\Payment
 * @property FileTemplate $template
 */
class GroupControl extends AbstractDetailControl {
    /**
     * @var ModelScheduleGroup
     */
    private $model;

    /**
     * @param ModelScheduleGroup $group
     */
    public function setGroup(ModelScheduleGroup $group) {
        $this->model = $group;
    }

    public function render() {
        $this->template->model = $this->model;
        $this->template->setTranslator($this->translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'GroupControl.latte');
        $this->template->render();
    }
}
