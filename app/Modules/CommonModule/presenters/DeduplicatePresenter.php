<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Grids\Deduplicate\PersonsGrid;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\UI\PageTitle;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Table\ActiveRow;
use Persons\Deduplication\DuplicateFinder;
use Persons\Deduplication\Merger;

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

    /**
     * @param ServicePerson $servicePerson
     * @return void
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }

    /**
     * @param Merger $merger
     * @return void
     */
    public function injectMerger(Merger $merger) {
        $this->merger = $merger;
    }

    /**
     * @return void
     */
    public function authorizedPerson() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowedForAnyContest('person', 'list'));
    }

    /**
     * @return void
     */
    public function titlePerson() {
        $this->setPageTitle(new PageTitle(_('Duplicitní osoby'), 'fa fa-exchange'));
    }

    /**
     * @throws ForbiddenRequestException
     * @throws AbortException
     * @throws BadRequestException
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
            /** @var ActiveRow $mergedRow */
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

    protected function createComponentPersonsGrid(): PersonsGrid {
        $duplicateFinder = $this->createPersonDuplicateFinder();
        $pairs = $duplicateFinder->getPairs();
        $trunkPersons = $this->servicePerson->getTable()->where('person_id', array_keys($pairs));

        return new PersonsGrid($trunkPersons, $pairs, $this->getContext());
    }

    protected function createPersonDuplicateFinder(): DuplicateFinder {
        return new DuplicateFinder($this->servicePerson, $this->globalParameters);
    }
}
