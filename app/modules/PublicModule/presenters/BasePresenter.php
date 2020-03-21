<?php

namespace PublicModule;

use FKSDB\Components\Controls\ContestChooser;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelRole;
use Nette\Application\BadRequestException;

/**
 * Current year of FYKOS.
 *
 * @todo Contest should be from URL and year should be current.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends \ContestPresenter {

    /**
     * @var ModelContestant|null|false
     */
    private $contestant = false;

    /**
     * @return ContestChooser
     */
    protected function createComponentContestChooser(): ContestChooser {
        $control = new ContestChooser($this->session, $this->yearCalculator, $this->serviceContest);
        $control->setContests(ModelRole::CONTESTANT);
        return $control;
    }

    /**
     * @return false|\FKSDB\ORM\Models\ModelContestant|null
     * @throws BadRequestException
     */
    public function getContestant() {
        if ($this->contestant === false) {
            /**
             * @var \FKSDB\ORM\Models\ModelPerson $person
             */
            $person = $this->user->getIdentity()->getPerson();
            $contestant = $person->related(DbNames::TAB_CONTESTANT_BASE, 'person_id')->where(array(
                'contest_id' => $this->getSelectedContest()->contest_id,
                'year' => $this->getSelectedYear()
            ))->fetch();

            $this->contestant = $contestant ? ModelContestant::createFromActiveRow($contestant) : null;
        }

        return $this->contestant;
    }

    /**
     * @return string[]
     */
    public function getNavRoots(): array {
        return ['public.dashboard.default'];
    }
}
