<?php

namespace OrgModule;

use FKSDB\Components\Forms\Factories\OrgFactory;
use FKSDB\Components\Grids\OrgsGrid;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelOrg;
use FKSDB\ORM\Services\ServiceOrg;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Persons\ExtendedPersonHandler;

/**
 * Class OrgPresenter
 * *
 * @method ModelOrg getModel2(int $id = null)
 */
class OrgPresenter extends ExtendedPersonPresenter {
    /** @var string */
    protected $fieldsDefinition = 'adminOrg';
    /**
     * @var int
     * @persistent
     */
    public $id;
    /**
     * @var ServiceOrg
     */
    private $serviceOrg;

    /**
     * @var OrgFactory
     */
    private $orgFactory;

    /**
     * @param ServiceOrg $serviceOrg
     * @return void
     */
    public function injectServiceOrg(ServiceOrg $serviceOrg) {
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @param OrgFactory $orgFactory
     * @return void
     */
    public function injectOrgFactory(OrgFactory $orgFactory) {
        $this->orgFactory = $orgFactory;
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function titleEdit(int $id) {
        $this->setTitle(sprintf(_('Úprava organizátora %s'), $this->getModel2($id)->getPerson()->getFullName()), 'fa fa-pencil');
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function titleDetail(int $id) {
        $this->setTitle(sprintf(_('Org %s'), $this->getModel2($id)->getPerson()->getFullName()), 'fa fa-user');
    }

    public function titleCreate() {
        $this->setTitle(_('Založit organizátora'), 'fa fa-user-plus');
    }

    public function titleList() {
        $this->setTitle(_('Organizátoři'), 'fa fa-address-book');
    }

    /**
     * @param int $id
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    public function actionEdit(int $id) {
        $org = $this->getModel2($id);

        if ($org->contest_id != $this->getSelectedContest()->contest_id) {
            throw new ForbiddenRequestException(_('Editace organizátora mimo zvolený seminář.'));
        }
    }

    /**
     * @param int $id
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        $this->template->model = $this->getModel2($id);
    }

    /**
     * @param IModel|null $model
     * @param Form $form
     * @throws BadRequestException
     */
    protected function setDefaults(IModel $model = null, Form $form) {
        parent::setDefaults($model, $form);
        if (!$model) {
            return;
        }
        $defaults = [];
        $defaults[ExtendedPersonHandler::CONT_MODEL]['since'] = $this->getSelectedYear();
        $form[ExtendedPersonHandler::CONT_MODEL]->setDefaults($defaults);
    }

    /**
     * @return OrgsGrid
     * @throws BadRequestException
     */
    protected function createComponentGrid(): OrgsGrid {
        return new OrgsGrid($this->getContext(), $this->getSelectedContest());
    }

    /**
     * @param Form $form
     * @return void
     * @throws BadRequestException
     * @throws \Exception
     */
    protected function appendExtendedContainer(Form $form) {
        $container = $this->orgFactory->createOrg($this->getSelectedContest());
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    protected function getORMService(): ServiceOrg {
        return $this->serviceOrg;
    }

    public function messageCreate(): string {
        return _('Organizátor %s založen.');
    }

    public function messageEdit(): string {
        return _('Organizátor %s upraven.');
    }

    public function messageError(): string {
        return _('Chyba při zakládání organizátora.');
    }

    public function messageExists(): string {
        return _('Organizátor již existuje.');
    }

    protected function getModelResource(): string {
        return ModelOrg::RESOURCE_ID;
    }
}
