<?php

namespace OrgModule;

use FKSDB\Components\Forms\Factories\OrgFactory;
use FKSDB\Components\Grids\OrgsGrid;
use Nette\Application\UI\Form;
use ORM\IModel;
use Persons\ExtendedPersonHandler;
use ServiceOrg;

/**
 * Class OrgPresenter
 * @package OrgModule
 */
class OrgPresenter extends ExtendedPersonPresenter {

    protected $modelResourceId = 'org';
    protected $fieldsDefinition = 'adminOrg';

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
     */
    public function injectServiceOrg(ServiceOrg $serviceOrg) {
        $this->serviceOrg = $serviceOrg;
    }

    /**
     * @param OrgFactory $orgFactory
     */
    public function injectOrgFactory(OrgFactory $orgFactory) {
        $this->orgFactory = $orgFactory;
    }

    /**
     * @param $id
     */
    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava organizátora %s'), $this->getModel()->getPerson()->getFullname()));
    }

    /**
     * @param $id
     * @throws \Nette\Application\BadRequestException
     */
    public function renderEdit($id) {
        parent::renderEdit($id);

        $org = $this->getModel();

        if ($org->contest_id != $this->getSelectedContest()->contest_id) {
            $this->flashMessage(_('Editace organizátora mimo zvolený seminář.'), self::FLASH_WARNING);
        }
    }

    public function titleCreate() {
        $this->setTitle(_('Založit organizátora'));
        $this->setIcon('fa fa-user-plus');
    }

    public function titleList() {
        $this->setTitle(_('Organizátoři'));
        $this->setIcon('fa fa-address-book');
    }

    /**
     * @param IModel|null $model
     * @param Form $form
     * @throws \Nette\Application\BadRequestException
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
     * @param $name
     * @return OrgsGrid|mixed
     */
    protected function createComponentGrid($name) {
        $grid = new OrgsGrid($this->serviceOrg);

        return $grid;
    }

    /**
     * @param Form $form
     * @return mixed|void
     * @throws \Nette\Application\BadRequestException
     */
    protected function appendExtendedContainer(Form $form) {
        $container = $this->orgFactory->createOrg(0, null, $this->getSelectedContest());
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    /**
     * @return mixed|ServiceOrg
     */
    protected function getORMService() {
        return $this->serviceOrg;
    }

    /**
     * @return string
     */
    public function messageCreate() {
        return _('Organizátor %s založen.');
    }

    /**
     * @return string
     */
    public function messageEdit() {
        return _('Organizátor %s upraven.');
    }

    /**
     * @return string
     */
    public function messageError() {
        return _('Chyba při zakládání organizátora.');
    }

    /**
     * @return string
     */
    public function messageExists() {
        return _('Organizátor již existuje.');
    }

}

