<?php

namespace FKSDB\Modules\CommonModule;

use FKSDB\Components\Controls\Entity\School\SchoolFormComponent;
use FKSDB\Components\Grids\ContestantsFromSchoolGrid;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceSchool;
use FKSDB\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\IResource;

/**
 * Class SchoolPresenter
 * *
 * @method ModelSchool getEntity()
 */
class SchoolPresenter extends BasePresenter {
    use EntityPresenterTrait;

    /** @var ServiceSchool */
    private $serviceSchool;

    /**
     * @param ServiceSchool $serviceSchool
     * @return void
     */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('Schools'), 'fa fa-university');
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Create school'), 'fa fa-plus');
    }

    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit school %s'), $this->getEntity()->name_abbrev), 'fa fa-pencil'));
    }

    /**
     * @return void
     *
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Detail of school %s'), $this->getEntity()->name_abbrev), 'fa fa-university'));
    }

    /**
     * @throws ModelNotFoundException
     * @throws BadTypeException
     */
    public function actionEdit(): void {
        $this->traitActionEdit();
    }

    /**
     * @return void
     * @throws ModelNotFoundException
     */
    public function renderDetail(): void {
        $this->template->model = $this->getEntity();
    }

    protected function createComponentGrid(): SchoolsGrid {
        return new SchoolsGrid($this->getContext());
    }

    protected function createComponentEditForm(): SchoolFormComponent {
        return new SchoolFormComponent($this->getContext(), false);
    }

    protected function createComponentCreateForm(): SchoolFormComponent {
        return new SchoolFormComponent($this->getContext(), true);
    }

    /**
     * @return ContestantsFromSchoolGrid
     * @throws ModelNotFoundException
     */
    protected function createComponentContestantsFromSchoolGrid(): ContestantsFromSchoolGrid {
        return new ContestantsFromSchoolGrid($this->getEntity(), $this->getContext());
    }

    /**
     * @param IResource|string $resource
     * @param string $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }

    protected function getORMService(): ServiceSchool {
        return $this->serviceSchool;
    }
}
