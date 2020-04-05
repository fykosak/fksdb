<?php

namespace CommonModule;

use FKSDB\Components\Controls\Entity\School\CreateForm;
use FKSDB\Components\Controls\Entity\School\EditForm;
use FKSDB\Components\Grids\SchoolsGrid;
use FKSDB\EntityTrait;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Services\ServiceSchool;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Security\IResource;

/**
 * Class SchoolPresenter
 * @package CommonModule
 * @method ModelSchool getEntity()
 * @method ModelSchool loadEntity(int $id)
 */
class SchoolPresenter extends BasePresenter {
    use EntityTrait;

    /** @var ServiceSchool */
    private $serviceSchool;

    /** @param ServiceSchool $serviceSchool */
    public function injectServiceSchool(ServiceSchool $serviceSchool) {
        $this->serviceSchool = $serviceSchool;
    }

    public function titleList() {
        $this->setTitle(_('Schools'), 'fa fa-university');
    }

    public function titleCreate() {
        $this->setTitle(_('Založit školu'), 'fa fa-plus');
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function titleEdit(int $id) {
        $this->setTitle(sprintf(_('Úprava školy %s'), $this->loadEntity($id)->name_abbrev), 'fa fa-pencil');
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function titleDetail(int $id) {
        $this->setTitle(sprintf(_('Detail of school %s'), $this->loadEntity($id)->name_abbrev), 'fa fa-university');
    }

    /**
     * @inheritDoc
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isAnyContestAuthorized($resource, $privilege);
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function actionEdit(int $id) {
        $this->traitActionEdit($id);
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @return IService|ServiceSchool
     */
    protected function getORMService() {
        return $this->serviceSchool;
    }

    /** @return SchoolsGrid */
    protected function createComponentGrid(): SchoolsGrid {
        return new SchoolsGrid($this->getContext());
    }

    /** @inheritDoc */
    public function createComponentEditForm(): Control {
        return new EditForm($this->getContext());
    }

    /** @inheritDoc */
    public function createComponentCreateForm(): Control {
        return new CreateForm($this->getContext());
    }

}
