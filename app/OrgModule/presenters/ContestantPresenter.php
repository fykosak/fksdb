<?php

namespace OrgModule;

use FKSDB\Components\Grids\ContestantsGrid;
use Nette\Application\UI\Form;
use ServiceContestant;

/**
 * Class ContestantPresenter
 * @package OrgModule
 */
class ContestantPresenter extends ExtendedPersonPresenter {

    protected $modelResourceId = 'contestant';
    protected $fieldsDefinition = 'adminContestant';

    /**
     * @var ServiceContestant
     */
    private $serviceContestant;

    /**
     * @param ServiceContestant $serviceContestant
     */
    public function injectServiceContestant(ServiceContestant $serviceContestant) {
        $this->serviceContestant = $serviceContestant;
    }

    /**
     * @param $id
     */
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

    /**
     * @param $name
     * @return ContestantsGrid
     */
    protected function createComponentGrid($name) {
        $grid = new ContestantsGrid($this->serviceContestant);
        return $grid;
    }

    /**
     * @param Form $form
     * @return mixed|void
     */
    protected function appendExtendedContainer(Form $form) {
        // no container for contestant
    }

    /**
     * @return mixed|ServiceContestant
     */
    protected function getORMService() {
        return $this->serviceContestant;
    }

    /**
     * @return null
     */
    protected function getAcYearFromModel() {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        return $this->yearCalculator->getAcademicYear($this->serviceContest->findByPrimary($model->contest_id), $model->year);
    }

    /**
     * @return string
     */
    public function messageCreate() {
        return _('Řešitel %s založen.');
    }

    /**
     * @return string
     */
    public function messageEdit() {
        return _('Řešitel %s upraven.');
    }

    /**
     * @return string
     */
    public function messageError() {
        return _('Chyba při zakládání řešitele.');
    }

    /**
     * @return string
     */
    public function messageExists() {
        return _('Řešitel už existuje.');
    }


}

