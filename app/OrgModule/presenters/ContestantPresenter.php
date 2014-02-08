<?php

namespace OrgModule;

use FKSDB\Components\Grids\ContestantsGrid;
use Nette\Application\UI\Form;
use ServiceContestant;

class ContestantPresenter extends ExtendedPersonPresenter {

    protected $modelResourceId = 'contestant';
    protected $fieldsDefinition = 'adminContestant';

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava řešitele %s'), $this->getModel()->getPerson()->getFullname()));
    }

    public function titleCreate() {
        $this->setTitle(_('Založit řešitele'));
    }

    public function titleList() {
        $this->setTitle(_('Řešitelé'));
    }

    protected function createComponentGrid($name) {
        $grid = new ContestantsGrid($this->serviceContestant);
        return $grid;
    }

    protected function appendExtendedContainer(Form $form) {
        // no container for contestant
    }

    protected function getORMService() {
        return $this->serviceContestant;
    }

    protected function getAcYearFromModel() {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        return $this->yearCalculator->getAcademicYear($this->serviceContest->findByPrimary($model->contest_id), $model->year);
    }

}

