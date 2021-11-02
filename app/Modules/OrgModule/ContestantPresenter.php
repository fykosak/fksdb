<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Models\ORM\Models\ModelContestant;
use FKSDB\Models\ORM\Models\ModelContestYear;
use FKSDB\Models\ORM\Services\ServiceContestant;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Form;

/**
 * @method ModelContestant getModel()
 */
class ContestantPresenter extends ExtendedPersonPresenter
{

    protected string $fieldsDefinition = 'adminContestant';

    private ServiceContestant $serviceContestant;

    final public function injectServiceContestant(ServiceContestant $serviceContestant): void
    {
        $this->serviceContestant = $serviceContestant;
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            sprintf(_('Edit the contestant %s'), $this->getModel()->getPerson()->getFullName()),
            'fa fa-user-edit'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Create contestant'), 'fa fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Contestants'), 'fa fa-user-graduate');
    }

    public function messageCreate(): string
    {
        return _('Contestant %s created.');
    }

    public function messageEdit(): string
    {
        return _('Contestant %s modified.');
    }

    public function messageError(): string
    {
        return _('Error while creating the contestant.');
    }

    public function messageExists(): string
    {
        return _('Contestant already exists.');
    }

    protected function createComponentGrid(): ContestantsGrid
    {
        return new ContestantsGrid($this->getContext(), $this->getSelectedContestYear());
    }

    protected function appendExtendedContainer(Form $form): void
    {
        // no container for contestant
    }

    protected function getORMService(): ServiceContestant
    {
        return $this->serviceContestant;
    }

    protected function getAcYearFromModel(): ?ModelContestYear
    {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        return $model->getContestYear();
    }

    protected function getModelResource(): string
    {
        return ModelContestant::RESOURCE_ID;
    }
}
