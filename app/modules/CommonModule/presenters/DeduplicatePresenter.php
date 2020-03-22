<?php

namespace CommonModule;

use FKSDB\Components\Grids\Deduplicate\PersonsGrid;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\ForbiddenRequestException;
use Persons\Deduplication\DuplicateFinder;
use Persons\Deduplication\Merger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DeduplicatePresenter extends BasePresenter {

    /**
     * @var \FKSDB\ORM\Services\ServicePerson
     */
    private $servicePerson;

    /**
     * @var Merger
     */
    private $merger;

    /**
     * @param ServicePerson $servicePerson
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param Merger $merger
     */
    public function injectMerger(Merger $merger) {
        $this->merger = $merger;
    }

    /**
     */
    public function authorizedPerson() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowedForAnyContest('person', 'list'));
    }

    public function titlePerson() {
        $this->setTitle(_('Duplicitní osoby'));
        $this->setIcon('fa fa-exchange');
    }

    /**
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function handleBatchMerge() {
        if (!$this->getContestAuthorizator()->isAllowedForAnyContest('person', 'merge')) { //TODO generic authorizator
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

    /**
     * @param $name
     * @return PersonsGrid
     */
    protected function createComponentPersonsGrid($name) {
        $duplicateFinder = $this->createPersonDuplicateFinder();
        $pairs = $duplicateFinder->getPairs();
        $trunkPersons = $this->servicePerson->getTable()->where('person_id', array_keys($pairs));

        $grid = new PersonsGrid($trunkPersons, $pairs, $this->getContext());

        return $grid;
    }


    /**
     * @return DuplicateFinder
     */
    protected function createPersonDuplicateFinder() {
        return new DuplicateFinder($this->servicePerson, $this->globalParameters);
    }

}
