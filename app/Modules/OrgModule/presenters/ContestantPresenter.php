<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\Models\ModelContestant;
use FKSDB\ORM\Services\ServiceContestant;
use FKSDB\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;

/**
 * Class ContestantPresenter
 * *
 * @method ModelContestant getModel()
 */
class ContestantPresenter extends ExtendedPersonPresenter {
    /** @var string */
    protected $fieldsDefinition = 'adminContestant';

    private ServiceContestant $serviceContestant;
    
    public function injectServiceContestant(ServiceContestant $serviceContestant): void {
        $this->serviceContestant = $serviceContestant;
    }

    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Úprava řešitele %s'), $this->getModel()->getPerson()->getFullName()), 'fa fa-user'));
    }

    public function titleCreate(): void {
        $this->setPageTitle(new PageTitle(_('Založit řešitele'), 'fa fa-user-plus'));
    }

    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('Řešitelé'), 'fa fa-users'));
    }

    /**
     * @return ContestantsGrid
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     */
    protected function createComponentGrid(): ContestantsGrid {
        return new ContestantsGrid($this->getContext(), $this->getSelectedContest(), $this->getSelectedYear());
    }

    protected function appendExtendedContainer(Form $form): void {
        // no container for contestant
    }

    protected function getORMService(): ServiceContestant {
        return $this->serviceContestant;
    }

    protected function getAcYearFromModel(): ?int {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        return $this->getYearCalculator()->getAcademicYear($this->getServiceContest()->findByPrimary($model->contest_id), $model->year);
    }

    public function messageCreate(): string {
        return _('Řešitel %s založen.');
    }

    public function messageEdit(): string {
        return _('Řešitel %s upraven.');
    }

    public function messageError(): string {
        return _('Chyba při zakládání řešitele.');
    }

    public function messageExists(): string {
        return _('Řešitel už existuje.');
    }

    protected function getModelResource(): string {
        return ModelContestant::RESOURCE_ID;
    }
}
