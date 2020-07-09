<?php

namespace FKSDB\Modules\PublicModule;

use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\ContestPresenter\ContestPresenter;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelRole;
use Nette\Application\ForbiddenRequestException;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends ContestPresenter {

    /**
     * @var ModelContestant|null
     */
    private $contestant;

    protected function createComponentContestChooser(): ContestChooser {
        $control = new ContestChooser($this->getContext());
        $control->setContests(ModelRole::CONTESTANT);
        return $control;
    }

    /**
     * @return ModelContestant|null
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    public function getContestant() {
        if (!isset($this->contestant) || is_null($this->contestant)) {
            /** @var ModelPerson $person */
            $person = $this->user->getIdentity()->getPerson();
            $contestant = $person->related(DbNames::TAB_CONTESTANT_BASE, 'person_id')->where([
                'contest_id' => $this->getSelectedContest()->contest_id,
                'year' => $this->getSelectedYear(),
            ])->fetch();

            $this->contestant = $contestant ? ModelContestant::createFromActiveRow($contestant) : null;
        }
        return $this->contestant;
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array {
        return ['Public.Dashboard.default'];
    }
}
