<?php

namespace OrgModule;

use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Services\ServiceContestant;
use Nette\Application\UI\Form;

/**
 * Class ContestantPresenter
 * @package OrgModule
 * @method ModelContestant getModel()
 */
class ContestantPresenter extends ExtendedPersonPresenter {

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
        $this->setTitle(sprintf(_('Úprava řešitele %s'), $this->getModel()->getPerson()->getFullName()), 'fa fa-user');
    }

    public function titleCreate() {
        $this->setTitle(_('Založit řešitele'), 'fa fa-user-plus');
    }

    public function titleList() {
        $this->setTitle(_('Řešitelé'), 'fa fa-users');
    }

    /**
     * @return ContestantsGrid
     */
    protected function createComponentGrid() {
        return new ContestantsGrid($this->getContext());
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
     * TODO refactoring
     */
    protected function getAcYearFromModel() {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        return $this->yearCalculator->getAcademicYear($this->getServiceContest()->findByPrimary($model->contest_id), $model->year);
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


    /**
     * @inheritDoc
     */
    protected function getModelResource(): string {
        return ModelContestant::RESOURCE_ID;
    }
}

