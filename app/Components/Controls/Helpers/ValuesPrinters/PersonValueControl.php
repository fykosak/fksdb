<?php


namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\Stalking\Helpers\PersonLinkControl;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class PersonValueControl
 * @package FKSDB\Components\Controls\Helpers\ValuePrinters
 */
class PersonValueControl extends AbstractValue {
    /**
     * @param ModelPerson $person
     * @param int $year
     * @param int $contestId
     */
    public function render(ModelPerson $person, int $year, int $contestId) {
        $this->beforeRender(_('Person'), true);
        $this->template->person = $person;
        $this->template->year = $year;
        $this->template->contestId = $contestId;
        $this->template->setFile(__DIR__ . '/PersonValue.latte');
        $this->template->render();
    }

    /**
     * @return PersonLinkControl
     */
    protected function createComponentPersonLink(): PersonLinkControl {
        return new PersonLinkControl();
    }
}
