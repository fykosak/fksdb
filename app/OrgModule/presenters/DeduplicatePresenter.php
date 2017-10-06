<?php

namespace OrgModule;

use FKSDB\Components\Grids\Deduplicate\PersonsGrid;
use Nette\Application\ForbiddenRequestException;
use Persons\Deduplication\DuplicateFinder;
use Persons\Deduplication\Merger;
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

    /**
     * @var Merger
     */
    private $merger;

    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    public function injectMerger(Merger $merger) {
        $this->merger = $merger;
    }

    public function authorizedPerson() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('person', 'list', $this->getSelectedContest()));
    }

    public function titlePerson() {
        $this->setIcon('<i class="fa fa-exchange" aria-hidden="true"></i>');
        $this->setTitle(_('Duplicitní osoby'));
    }

    public function actionPerson() {

    }

    public function handleBatchMerge() {
        if (!$this->getContestAuthorizator()->isAllowed('person', 'merge', $this->getSelectedContest())) { //TODO generic authorizator
            throw new ForbiddenRequestException();
        }
        //TODO later specialize for each entinty type
        $finder = $this->createPersonDuplicateFinder();
        $pairs = $finder->getPairs();
        $trunkPersons = $this->servicePerson->getTable()->where('person_id', array_keys($pairs));
        $table = $this->servicePerson->getTable()->getName();

        foreach ($pairs as $trunkId => $mergedData) {
            if (!isset($trunkPersons[$trunkId])) {
                continue; // the trunk can be already merged somewhere else as merged
            }
            $trunkRow = $trunkPersons[$trunkId];
            $mergedRow = $mergedData[DuplicateFinder::IDX_PERSON];
            $this->merger->setMergedPair($trunkRow, $mergedRow);

            if ($this->merger->merge()) {
                $this->flashMessage(sprintf(_('%s (%d) a %s (%d) sloučeny.'), $table, $trunkRow->getPrimary(), $table, $mergedRow->getPrimary()), self::FLASH_SUCCESS);
            } else {
                $this->flashMessage(sprintf(_('%s (%d) a %s (%d) potřebují vyřešit konflitky.'), $table, $trunkRow->getPrimary(), $table, $mergedRow->getPrimary()), self::FLASH_INFO);
            }
        }

        $this->redirect('this');
    }

    protected function createComponentPersonsGrid($name) {
        $duplicateFinder = $this->createPersonDuplicateFinder();
        $pairs = $duplicateFinder->getPairs();
        $trunkPersons = $this->servicePerson->getTable()->where('person_id', array_keys($pairs));

        $grid = new PersonsGrid($trunkPersons, $pairs);

        return $grid;
    }

    protected function createPersonDuplicateFinder() {
        return new DuplicateFinder($this->servicePerson, $this->globalParameters);
    }

}
