<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use FKSDB\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 * Class ScheduleItemPresenter
 * *
 * @method ModelScheduleItem traitGetEntity()
 */
class ScheduleItemPresenter extends BasePresenter {
    use EventEntityPresenterTrait {
        getEntity as traitGetEntity;
    }

    /**
     * @var int
     * @persistent
     */
    public $groupId;
    /** @var ModelScheduleGroup */
    private $group;

    private ServiceScheduleItem $serviceScheduleItem;

    private ServiceScheduleGroup $serviceScheduleGroup;

    public function injectServiceScheduleItem(ServiceScheduleItem $serviceScheduleItem): void {
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    public function injectServiceScheduleGroup(ServiceScheduleGroup $serviceScheduleGroup): void {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(\sprintf(_('Schedule items')), 'fa fa-calendar-check-o');
    }

    /**
     * @return PageTitle
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    public function getTitleDetail(): PageTitle {
        $item = $this->getEntity();
        return new PageTitle(\sprintf(_('Schedule item "%s/%s"'), $item->name_cs, $item->name_en), 'fa fa-calendar-check-o');
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    public function actionDetail(): void {
        $this->getEntity();
    }

    /**
     * @throws InvalidStateException
     */
    public function renderList(): void {
        $this->template->group = $this->getGroup();
    }

    /**
     *
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    public function renderDetail(): void {
        $this->template->group = $this->getGroup();
        $this->template->model = $this->getEntity();
    }

    /**
     * @return ModelScheduleItem
     *
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    protected function getEntity(): ModelScheduleItem {
        $entity = $this->traitGetEntity();
        if ($entity->schedule_group_id !== $this->getGroup()->schedule_group_id) {
            throw new ForbiddenRequestException();
        }
        return $entity;
    }

    /**
     * @return ModelScheduleGroup
     * @throws InvalidStateException
     */
    private function getGroup(): ModelScheduleGroup {
        if (!$this->group) {
            $group = $this->serviceScheduleGroup->findByPrimary($this->groupId);
            if (!$group) {
                throw new InvalidStateException();
            }
            $this->group = $group;
        }
        return $this->group;
    }

    /**
     * @return Control
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @return Control
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): BaseGrid {
        return new ItemsGrid($this->getContext(), $this->getGroup());
    }

    /**
     * @return PersonsGrid
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws EventNotFoundException
     * @throws BadTypeException
     */
    protected function createComponentPersonsGrid(): PersonsGrid {
        return new PersonsGrid($this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ServiceScheduleItem {
        return $this->serviceScheduleItem;
    }

    /**
     * @param string|IResource $resource
     * @param string $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /**
     * @param PageTitle $pageTitle
     * @return void
     * @throws EventNotFoundException
     */
    protected function setPageTitle(PageTitle $pageTitle): void {
        $pageTitle->subTitle .= ' ->' . sprintf('"%s/%s"', $this->getGroup()->name_cs, $this->getGroup()->name_en);
        parent::setPageTitle($pageTitle);
    }
}
