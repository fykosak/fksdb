<?php

namespace EventModule;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\Schedule\ItemsGrid;
use FKSDB\Components\Grids\Schedule\PersonsGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\Schedule\ModelScheduleGroup;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\InvalidStateException;

/**
 * Class ScheduleItemPresenter
 * @package EventModule
 * @method ModelScheduleItem traitLoadEntity(int $id)
 */
class ScheduleItemPresenter extends BasePresenter {
    use EventEntityTrait {
        loadEntity as traitLoadEntity;
    }
    /**
     * @var int
     * @persistent
     */
    public $groupId;
    /**
     * @var ModelScheduleGroup
     */
    private $group;
    /**
     * @var ServiceScheduleItem
     */
    private $serviceScheduleItem;

    /**
     * @var ServiceScheduleGroup
     */
    private $serviceScheduleGroup;

    /**
     * @param ServiceScheduleItem $serviceScheduleItem
     * @return void
     */
    public function injectServiceScheduleItem(ServiceScheduleItem $serviceScheduleItem) {
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    /**
     * @param ServiceScheduleGroup $serviceScheduleGroup
     * @return void
     */
    public function injectServiceScheduleGroup(ServiceScheduleGroup $serviceScheduleGroup) {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
    }

    public function titleList() {
        $this->setTitle(\sprintf(_('Schedule items')), 'fa fa-calendar-check-o');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail(int $id) {
        $item = $this->loadEntity($id);
        $this->setTitle(\sprintf(_('Schedule item "%s/%s"'), $item->name_cs, $item->name_en), 'fa fa-calendar-check-o');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail(int $id) {
        $component = $this->getComponent('personsGrid');
        if (!$component instanceof PersonsGrid) {
            throw new BadTypeException(PersonsGrid::class, $component);
        }
        $component->setItem($this->loadEntity($id));
    }

    /**
     * @throws InvalidStateException
     */
    public function renderList() {
        $this->template->group = $this->getGroup();
    }

    /**
     * @param int $id
     * @throws BadRequestException
     * @throws AbortException
     * @throws ForbiddenRequestException
     */
    public function renderDetail(int $id) {
        $this->template->group = $this->getGroup();
        $this->template->model = $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @return ModelScheduleItem
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws AbortException
     */
    protected function loadEntity(int $id) {
        $entity = $this->traitLoadEntity($id);
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
     * @inheritDoc
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     * @throws InvalidStateException
     */
    protected function createComponentGrid(): BaseGrid {
        return new ItemsGrid($this->getContext(), $this->getGroup());
    }

    public function createComponentPersonsGrid(): PersonsGrid {
        return new PersonsGrid($this->getContext());
    }

    /**
     * @inheritDoc
     */
    protected function getORMService() {
        return $this->serviceScheduleItem;
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /**
     * @param string $title
     * @param string $icon
     * @param string $subTitle
     * @throws BadRequestException
     */
    protected function setTitle(string $title, string $icon = '', string $subTitle = '') {
        parent::setTitle($title, $icon, $subTitle . ' ->' . sprintf('"%s/%s"', $this->getGroup()->name_cs, $this->getGroup()->name_en));
    }
}
