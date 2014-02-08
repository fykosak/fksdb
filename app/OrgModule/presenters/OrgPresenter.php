<?php

namespace OrgModule;

use FKSDB\Components\Forms\Factories\OrgFactory;
use FKSDB\Components\Grids\OrgsGrid;
use Nette\Application\UI\Form;
use ORM\IModel;
use Persons\ExtendedPersonHandler;
use ServiceOrg;

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

    public function injectServiceOrg(ServiceOrg $serviceOrg) {
        $this->serviceOrg = $serviceOrg;
    }

    public function injectOrgFactory(OrgFactory $orgFactory) {
        $this->orgFactory = $orgFactory;
    }

    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava organizátora %s'), $this->getModel()->getPerson()->getFullname()));
    }

    public function renderEdit($id) {
        parent::renderEdit($id);

        $org = $this->getModel();

        if ($org->contest_id != $this->getSelectedContest()->contest_id) {
            $this->flashMessage(_('Editace organizátora mimo zvolený seminář.'), self::FLASH_WARNING);
        }
    }

    public function titleCreate() {
        $this->setTitle(_('Založit organizátora'));
    }

    public function titleList() {
        $this->setTitle(_('Organizátoři'));
    }

    protected function setDefaults(IModel $model = null, Form $form) {
        parent::setDefaults($model, $form);
        if (!$model) {
            $defaults = array();
            $defaults[ExtendedPersonHandler::CONT_MODEL]['since'] = $this->getSelectedYear();
            $form[ExtendedPersonHandler::CONT_MODEL]->setDefaults($defaults);
        }
    }

    protected function createComponentGrid($name) {
        $grid = new OrgsGrid($this->serviceOrg);

        return $grid;
    }

    protected function appendExtendedContainer(Form $form) {
        $container = $this->orgFactory->createOrg(0, null, $this->getSelectedContest());
        $form->addComponent($container, ExtendedPersonHandler::CONT_MODEL);
    }

    protected function getORMService() {
        return $this->serviceOrg;
    }

}

