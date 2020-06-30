<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\Org\OrgForm;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use FKSDB\UI\PageTitle;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;

/**
 * Class OrgPresenter
 * *
 * @method ModelOrg traitGetEntity()
 */
class OrgPresenter extends BasePresenter {

    use EntityPresenterTrait {
        getEntity as traitGetEntity;
    }

    /**
     * @var ServiceOrg
     */
    private $serviceOrg;

    /**
     * @param ServiceOrg $serviceOrg
     * @return void
     */
    public function injectServiceOrg(ServiceOrg $serviceOrg) {
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleEdit() {
        $this->setPageTitle(new PageTitle(sprintf(_('Úprava organizátora %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-pencil'));
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function titleDetail() {
        $this->setPageTitle(new PageTitle(sprintf(_('Org %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-user'));
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Založit organizátora'), 'fa fa-user-plus');
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('Organizátoři'), 'fa fa-address-book');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function actionEdit() {
        $this->traitActionEdit();
    }

    /**
     * @return ModelOrg
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function getEntity(): ModelOrg {
        $entity = $this->traitGetEntity();
        if ($entity->contest_id != $this->getSelectedContest()->contest_id) {
            throw new ForbiddenRequestException(_('Editace organizátora mimo zvolený seminář.'));
        }
        return $entity;
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function renderDetail() {
        $this->template->model = $this->getEntity();
    }

    protected function getORMService(): ServiceOrg {
        return $this->serviceOrg;
    }

    protected function getModelResource(): string {
        return ModelOrg::RESOURCE_ID;
    }

    /**
     * @return Control
     * @throws BadRequestException
     */
    protected function createComponentCreateForm(): Control {
        return new OrgForm($this->getContext(), $this->getSelectedContest(), true);
    }

    /**
     * @return Control
     * @throws BadRequestException
     */
    protected function createComponentEditForm(): Control {
        return new OrgForm($this->getContext(), $this->getSelectedContest(), false);
    }

    /**
     * @return OrgsGrid
     * @throws BadRequestException
     */
    protected function createComponentGrid(): OrgsGrid {
        return new OrgsGrid($this->getContext(), $this->getSelectedContest());
    }


    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
