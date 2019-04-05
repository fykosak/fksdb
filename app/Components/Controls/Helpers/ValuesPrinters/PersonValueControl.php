<?php


namespace FKSDB\Components\Controls\Helpers\ValuePrinters;

use FKSDB\Components\Controls\Stalking\Helpers\PersonLinkControl;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\PresenterComponent;
use Nette\Utils\Html;

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

    /**
     * @param PresenterComponent $component
     * @param ModelPerson $person
     * @param int $year
     * @param int $contestId
     * @return Html
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public static function getGridValue(PresenterComponent $component, ModelPerson $person, int $year, int $contestId): Html {
        return Html::el('a')
            ->addAttributes(['href' => $component->getPresenter()->link(':Org:Stalking:view', [
                'contestId' => $contestId,
                'year' => $year,
                'id' => $person->person_id,
            ])])
            ->addText($person->getFullName());
    }
}
