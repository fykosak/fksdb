<?php

namespace OrgModule;

use FKSDB\Components\Controls\Entity\Org\OrgForm;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\EntityTrait;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;

/**
 * Class OrgPresenter
 * *
 * @method ModelOrg getEntity()
 */
class OrgPresenter extends BasePresenter {

    use EntityTrait;

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
     */
    public function titleEdit() {
        $this->setTitle(sprintf(_('Úprava organizátora %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-pencil');
    }

    /**
     * @return void
     */
    public function titleDetail() {
        $this->setTitle(sprintf(_('Org %s'), $this->getEntity()->getPerson()->getFullName()), 'fa fa-user');
    }

    /**
     * @return void
     */
    public function titleCreate() {
        $this->setTitle(_('Založit organizátora'), 'fa fa-user-plus');
    }

    /**
     * @return void
     */
    public function titleList() {
        $this->setTitle(_('Organizátoři'), 'fa fa-address-book');
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionEdit() {
        if ($this->getEntity()->contest_id != $this->getSelectedContest()->contest_id) {
            throw new ForbiddenRequestException(_('Editace organizátora mimo zvolený seminář.'));
        }
        $this->traitActionEdit();
    }

    /**
     * @return void
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
