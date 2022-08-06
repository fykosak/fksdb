<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Grids\ContestantsGrid;
use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Services\ContestantService;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Form;

/**
 * @method ContestantModel getModel()
 */
class ContestantPresenter extends ExtendedPersonPresenter
{

    protected string $fieldsDefinition = 'adminContestant';

    private ContestantService $contestantService;

    final public function injectServiceContestant(ContestantService $contestantService): void
    {
        $this->contestantService = $contestantService;
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(
            null,
            sprintf(_('Edit the contestant %s'), $this->getModel()->person->getFullName()),
            'fa fa-user-edit'
        );
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Create contestant'), 'fa fa-user-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Contestants'), 'fa fa-user-graduate');
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

    protected function getORMService(): ContestantService
    {
        return $this->contestantService;
    }

    protected function getAcYearFromModel(): ?ContestYearModel
    {
        $model = $this->getModel();
        if (!$model) {
            return null;
        }
        return $model->getContestYear();
    }

    protected function getModelResource(): string
    {
        return ContestantModel::RESOURCE_ID;
    }
}
