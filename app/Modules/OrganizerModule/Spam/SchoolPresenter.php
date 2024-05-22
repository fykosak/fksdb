<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule\Spam;

use FKSDB\Components\EntityForms\SchoolLabelFormComponent;
use FKSDB\Components\Grids\Spam\SchoolGrid;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\SchoolLabelModel;
use FKSDB\Models\ORM\Services\SchoolLabelService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class SchoolPresenter extends BasePresenter
{
    /** @phpstan-use EntityPresenterTrait<SchoolLabelModel> */
    use EntityPresenterTrait;

    private SchoolLabelService $schoolLabelService;

    public function injectSpamSchoolService(SchoolLabelService $schoolLabelService): void
    {
        $this->schoolLabelService = $schoolLabelService;
    }

    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit label %s'), $this->getEntity()->school_label_key), 'fas fa-pen');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Add school label'), 'fas fa-plus');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('School labels'), 'fas fa-school');
    }


    /**
     * @throws GoneException
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): SchoolLabelFormComponent
    {
        return new SchoolLabelFormComponent($this->getContext(), $this->getEntity());
    }

    protected function createComponentCreateForm(): SchoolLabelFormComponent
    {
        return new SchoolLabelFormComponent($this->getContext(), null);
    }

    protected function createComponentGrid(): Control
    {
        return new SchoolGrid($this->getContext());
    }

    protected function getORMService(): SchoolLabelService
    {
        return $this->schoolLabelService;
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }
}
