<?php

namespace OrgModule;

use FKSDB\Components\Grids\Deduplicate\PersonsGrid;
use Persons\Deduplication\DuplicateFinder;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DeduplicatePresenter extends BasePresenter {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function authorizedPerson() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('person', 'list', $this->getSelectedContest()));
    }

    public function titlePerson() {
        $this->setTitle(_('Duplicitní osoby'));
    }

    public function actionPerson() {
        
    }

    protected function createComponentPersonsGrid($name) {
        $duplicateFinder = new DuplicateFinder($this->servicePerson);
        $pairs = $duplicateFinder->getPairs();
        $trunkPersons = $this->servicePerson->getTable()->where('person_id', array_keys($pairs));

        $grid = new PersonsGrid($trunkPersons, $pairs);

        return $grid;
    }

}
