<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Components\EntityForms\Spam\SchoolLabelFormComponent;
use FKSDB\Components\Grids\SchoolLabelGrid;
use FKSDB\Models\Authorization\Resource\PseudoContestResource;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\SchoolLabelModel;
use FKSDB\Models\ORM\Services\SchoolLabelService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;

final class SchoolPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<SchoolLabelModel> */
    use EntityPresenterTrait;

    private SchoolLabelService $schoolLabelService;

    public function injectSpamSchoolService(SchoolLabelService $schoolLabelService): void
    {
        $this->schoolLabelService = $schoolLabelService;
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @throws NoContestAvailable
     */
    public function authorizedEdit(): bool
    {
        return $this->isAllowed(
            new PseudoContestResource($this->getEntity(), $this->getSelectedContest()),
            'edit'
        );
    }

    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit label %s'), $this->getEntity()->school_label_key), 'fas fa-pen');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->isAllowed(
            new PseudoContestResource(SchoolLabelModel::RESOURCE_ID, $this->getSelectedContest()),
            'create'
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Add school label'), 'fas fa-plus');
    }

    /**
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed(
            new PseudoContestResource(SchoolLabelModel::RESOURCE_ID, $this->getSelectedContest()),
            'list'
        );
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('School labels'), 'fas fa-school');
    }


    /**
     * @throws GoneException
     * @throws NotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentEditForm(): SchoolLabelFormComponent
    {
        return new SchoolLabelFormComponent($this->getContext(), $this->getEntity(), $this->getSelectedContestYear());
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function createComponentCreateForm(): SchoolLabelFormComponent
    {
        return new SchoolLabelFormComponent($this->getContext(), null, $this->getSelectedContestYear());
    }

    protected function createComponentGrid(): Control
    {
        return new SchoolLabelGrid($this->getContext());
    }

    protected function getORMService(): SchoolLabelService
    {
        return $this->schoolLabelService;
    }
}
