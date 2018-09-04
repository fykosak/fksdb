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
        $this->setIcon('fa fa-user');
    }

    public function titleCreate() {
        $this->setTitle(_('Založit řešitele'));
        $this->setIcon('fa fa-user-plus');
    }

    public function titleList() {
        $this->setTitle(_('Řešitelé'));
        $this->setIcon('fa fa-users');
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

    public function messageCreate() {
        return _('Řešitel %s založen.');
    }

    public function messageEdit() {
        return _('Řešitel %s upraven.');
    }

    public function messageError() {
        return _('Chyba při zakládání řešitele.');
    }

    public function messageExists() {
        return _('Řešitel už existuje.');
    }


}

