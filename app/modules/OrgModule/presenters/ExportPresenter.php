<?php

namespace OrgModule;

use AuthenticatedPresenter;
use Exports\StoredQuery;
use Exports\StoredQueryFactory;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\StoredQueryComponent;
use FKSDB\Components\Controls\StoredQueryTagCloud;
use FKSDB\Components\Forms\Factories\StoredQueryFactory as StoredQueryFormFactory;
use FKSDB\Components\Grids\StoredQueriesGrid;
use FKSDB\EntityTrait;
use FKSDB\Exceptions\NotFoundException;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryParameter;
use FormUtils;
use FKSDB\Exceptions\ModelException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;
use Nette\Utils\Strings;
use FKSDB\ORM\ServicesMulti\ServiceMStoredQueryTag;
use Tracy\Debugger;

/**
 * Class ExportPresenter
 * @method ModelStoredQuery traitGetEntity()
 */
class ExportPresenter extends SeriesPresenter {
    use EntityTrait {
        getEntity as traitGetEntity;
    }

    const CONT_CONSOLE = 'console';
    const CONT_PARAMS_META = 'paramsMeta';
    const CONT_META = 'meta';
    const SESSION_NS = 'sql';
    const PARAM_LOAD_FROM_SESSION = 'lfs';
    const PARAM_HTTP_AUTH = 'ha';

    /**
     * @var string
     * @persistent
     */
    public $qid;
    /**
     * @var int
     * @persistent
     */
    public $id;

    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @var ServiceStoredQueryParameter
     */
    private $serviceStoredQueryParameter;

    /**
     * @var ServiceMStoredQueryTag
     */
    private $serviceMStoredQueryTag;

    /**
     * @var StoredQueryFormFactory
     */
    private $storedQueryFormFactory;

    /**
     * @var StoredQueryFactory
     */
    private $storedQueryFactory;

    /**
     * @var StoredQuery
     */
    private $storedQuery;

    /**
     * @var ModelStoredQuery
     */
    private $patternQuery = false;

    /**
     * @param ServiceStoredQuery $serviceStoredQuery
     * @return void
     */
    public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery) {
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    /**
     * @param StoredQueryFormFactory $storedQueryFormFactory
     * @return void
     */
    public function injectStoredQueryFormFactory(StoredQueryFormFactory $storedQueryFormFactory) {
        $this->storedQueryFormFactory = $storedQueryFormFactory;
    }

    /**
     * @param ServiceStoredQueryParameter $serviceStoredQueryParameter
     * @return void
     */
    public function injectServiceStoredQueryParameter(ServiceStoredQueryParameter $serviceStoredQueryParameter) {
        $this->serviceStoredQueryParameter = $serviceStoredQueryParameter;
    }

    /**
     * @param ServiceMStoredQueryTag $serviceMStoredQueryTag
     * @return void
     */
    public function injectServiceMStoredQueryTag(ServiceMStoredQueryTag $serviceMStoredQueryTag) {
        $this->serviceMStoredQueryTag = $serviceMStoredQueryTag;
    }

    /**
     * @param StoredQueryFactory $storedQueryFactory
     * @return void
     */
    public function injectStoredQueryFactory(StoredQueryFactory $storedQueryFactory) {
        $this->storedQueryFactory = $storedQueryFactory;
    }
    /* ****************************** TITLES *****************************/

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCreate() {
        $this->setTitle(sprintf(_('Create query')), 'fa fa-pencil');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setTitle(_('Exports'), 'fa fa-database');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleEdit() {
        $this->setTitle(sprintf(_('Edit query "%s"'), $this->getPatternQuery()->getFQName()), 'fa fa-pencil');
    }

    /**
     * @throws BadRequestException
     */
    public function titleShow() {
        $this->setTitle(sprintf(_('Detail of query "%s"'), $this->getPatternQuery()->getFQName()), 'fa fa-database');
    }

    /**
     * @throws BadRequestException
     */
    public function titleExecute() {
        $this->setTitle(sprintf(_('%s'), $this->getPatternQuery()->getFQName()), 'fa fa-play-circle-o');
    }

    /* ****************************** AUTH ******************************/

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function authorizedCreate() {
        $this->setAuthorized(
            ($this->getContestAuthorizator()->isAllowed('storedQuery', 'create', $this->getSelectedContest()) &&
                $this->getContestAuthorizator()->isAllowed('export.adhoc', 'execute', $this->getSelectedContest()))
        );
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function authorizedShow() {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new NotFoundException('Neexistující dotaz.');
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($query, 'show', $this->getSelectedContest()));
    }

    /**
     * @return void
     * @throws NotFoundException
     */
    public function authorizedExecute() {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new NotFoundException('Neexistující dotaz.');
        }
        // proper authorization is done in StoredQueryComponent
    }

    /**
     * @return string
     */
    protected function getHttpRealm() {
        return 'FKSDB-export';
    }

    /**
     * @return bool|int|string
     */
    public function getAllowedAuthMethods() {
        $methods = parent::getAllowedAuthMethods();
        if ($this->getParameter(self::PARAM_HTTP_AUTH, false)) {
            $methods = $methods | AuthenticatedPresenter::AUTH_ALLOW_HTTP;
        }
        return $methods;
    }
    /* ***************************** ACTION ************************************/

    /**
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     * @throws InvalidLinkException
     */
    public function actionExecute() {
        $query = $this->getPatternQuery();
        $storedQuery = $this->storedQueryFactory->createQuery($this, $query);
        $this->setStoredQuery($storedQuery);

        if ($query && $this->getParameter('qid')) {
            $parameters = [];
            foreach ($this->getParameter() as $key => $value) {
                if (Strings::startsWith($key, StoredQueryComponent::PARAMETER_URL_PREFIX)) {
                    $parameters[substr($key, strlen(StoredQueryComponent::PARAMETER_URL_PREFIX))] = $value;
                }
            }
            /** @var StoredQueryComponent $storedQueryComponent */
            $storedQueryComponent = $this->getComponent('resultsComponent');
            $storedQueryComponent->updateParameters($parameters);

            if ($this->getParameter('format')) {
                $this->createRequest($storedQueryComponent, 'format!', ['format' => $this->getParameter('format')], 'forward');
                $this->forward($this->lastCreatedRequest);
            }
        }
    }

    /* ********************************* RENDER *****************************/
    /**
     * @return void
     * @throws BadRequestException
     */
    public function renderCreate() {
        $values = $this->getDesignFormFromSession();
        if ($values) {
            /** @var FormControl $control */
            $control = $this->getComponent('createForm');
            $control->getForm()->setDefaults($values);
        }
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function renderEdit() {
        $query = $this->getPatternQuery();

        $values = $this->getDesignFormFromSession();
        if (!$values) {
            $values = [];
            $values[self::CONT_CONSOLE] = $this->getPatternQuery();
            $values[self::CONT_META] = $this->getPatternQuery()->toArray();
            $values[self::CONT_META]['tags'] = $this->getPatternQuery()->getTags()->fetchPairs('tag_type_id', 'tag_type_id');
            $values[self::CONT_PARAMS_META] = [];

            foreach ($query->getParameters() as $parameter) {
                $paramData = $parameter->toArray();
                $paramData['default'] = $parameter->getDefaultValue();
                $values[self::CONT_PARAMS_META][] = $paramData;
            }
            if ($this->getPatternQuery()->getPostProcessing()) {
                $this->flashMessage(_('Výsledek dotazu je ještě zpracován v PHP. Dodržuj názvy sloupců a parametrů.'), BasePresenter::FLASH_WARNING);
            }
        }
        /** @var FormControl $control */
        $control = $this->getComponent('editForm');
        $control->getForm()->setDefaults($values);
    }

    /**
     * @return void
     */
    public function renderShow() {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    /**
     * @return void
     */
    public function renderExecute() {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    /**
     * @return StoredQuery
     * @throws BadRequestException
     */
    public function getStoredQuery() {
        if ($this->storedQuery) {
            return $this->storedQuery;
        } else {
            return $this->getStoredQueryFromSession();
        }
    }

    /**
     * @param StoredQuery $storedQuery
     * @return void
     */
    public function setStoredQuery(StoredQuery $storedQuery) {
        $this->storedQuery = $storedQuery; //TODO
    }

    /**
     * @param $values
     * @return void
     */
    private function storeDesignFormToSession($values) {
        $section = $this->session->getSection(self::SESSION_NS);
        $section->data = $values;
    }

    /**
     * @return array|null
     */
    private function getDesignFormFromSession() {
        // there may be invalid data in session, so we verify it by GET parameter
        if (!$this->getParameter(self::PARAM_LOAD_FROM_SESSION, false)) {
            return null;
        }
        $section = $this->session->getSection(self::SESSION_NS);
        return isset($section->data) ? $section->data : null;
    }

    private function clearSession() {
        $section = $this->session->getSection(self::SESSION_NS);
        unset($section->data);
    }

    /**
     * @return StoredQuery|null
     * @throws BadRequestException
     */
    protected function getStoredQueryFromSession() {
        $data = $this->getDesignFormFromSession();
        if (!$data) {
            return null;
        }

        $sql = $data[self::CONT_CONSOLE]['sql'];
        $parameters = [];
        foreach ($data[self::CONT_PARAMS_META] as $paramMetaData) {
            /** @var ModelStoredQueryParameter $parameter */
            $parameter = $this->serviceStoredQueryParameter->createNew($paramMetaData);
            $parameter->setDefaultValue($paramMetaData['default']);
            $parameters[] = $parameter;
        }

        return $this->storedQueryFactory->createQueryFromSQL($this, $sql, $parameters);
    }

    /**
     * @return ModelStoredQuery|null
     */
    public function getPatternQuery() {
        if ($this->patternQuery === false) {
            $id = $this->getParameter('id');
            $qId = $this->getParameter('qid');

            $this->patternQuery = $this->serviceStoredQuery->findByPrimary($id);
            if (!$this->patternQuery && $qId) {
                $this->patternQuery = $this->serviceStoredQuery->findByQid($qId);
            }
        }
        return $this->patternQuery;
    }

    /**
     * @return ModelStoredQuery|null
     * @throws InvalidStateException
     */
    public function getEntity() {
        try {
            $this->model = $this->traitGetEntity();
        } catch (InvalidStateException $exception) {
            $qId = $this->getParameter('qid');
            if ($qId) {
                $this->patternQuery = $this->serviceStoredQuery->findByQid($qId);
            } else {
                throw $exception;
            }
        }
        return $this->model;
    }


    protected function createComponentContestChooser(): ContestChooser {
        $component = parent::createComponentContestChooser();
        if ($this->getAction() == 'execute') {
            // Contest and year check is done in StoredQueryComponent
            $component->setContests(ContestChooser::CONTESTS_ALL);
            $component->setYears(ContestChooser::YEARS_ALL);
        }
        return $component;
    }

    protected function createComponentGrid(): StoredQueriesGrid {
        return new StoredQueriesGrid($this->getContext());
    }

    /**
     * @return StoredQueryComponent|null
     * @throws BadRequestException
     */
    protected function createComponentAdhocResultsComponent() {
        $storedQuery = $this->getStoredQuery();
        if ($storedQuery === null) { // workaround when session expires and persistent parameters from component are to be stored (because of redirect)
            return null;
        }
        $grid = new StoredQueryComponent($storedQuery, $this->getContext());
        $grid->setShowParametrize(false);
        return $grid;
    }

    /**
     * @return StoredQueryComponent|null
     * @throws BadRequestException
     */
    protected function createComponentResultsComponent() {
        $storedQuery = $this->getStoredQuery();
        // TODO is really needed?
        if ($storedQuery === null) { // workaround when session expires and persistent parameters from component are to be stored (because of redirect)
            return null;
        }
        return new StoredQueryComponent($storedQuery, $this->getContext());
    }

    protected function createComponentTagCloudList(): StoredQueryTagCloud {
        $tagCloud = new StoredQueryTagCloud(StoredQueryTagCloud::MODE_LIST, $this->serviceMStoredQueryTag);
        $tagCloud->registerOnClick($this->getComponent('grid')->getFilterByTagCallback());
        return $tagCloud;
    }

    protected function createComponentTagCloudDetail(): StoredQueryTagCloud {
        $tagCloud = new StoredQueryTagCloud(StoredQueryTagCloud::MODE_DETAIL, $this->serviceMStoredQueryTag);
        $tagCloud->setModelStoredQuery($this->getPatternQuery());
        return $tagCloud;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentCreateForm(): FormControl {
        $control = $this->createDesignForm();
        $control->getForm()->addSubmit('save', _('Save'))
            ->onClick[] = function (SubmitButton $button) {
            $this->handleCreateSuccess($button);
        };
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentEditForm(): FormControl {
        $control = $this->createDesignForm();
        $control->getForm()->addSubmit('save', _('Save'))
            ->onClick[] = function (SubmitButton $button) {
            $this->handleEditSuccess($button);
        };
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    private function createDesignForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $group = $form->addGroup(_('SQL'));

        $console = $this->storedQueryFormFactory->createConsole($group);
        $form->addComponent($console, self::CONT_CONSOLE);

        $params = $this->storedQueryFormFactory->createParametersMetadata($group);
        $form->addComponent($params, self::CONT_PARAMS_META);


        $group = $form->addGroup(_('Metadata'));

        $metadata = $this->storedQueryFormFactory->createMetadata($group);
        $form->addComponent($metadata, self::CONT_META);

        $form->setCurrentGroup();

        $submit = $form->addSubmit('execute', _('Execute'))
            ->setValidationScope(false);
        $submit->getControlPrototype()->setAttribute('class', 'btn-success');
        $submit->onClick[] = function (SubmitButton $button) {
            $this->handleCreateExecute($button);
        };

        return $control;
    }
    /* **************************** HANDLERS ******************/
    /**
     * @param SubmitButton $button
     * @return void
     * @throws AbortException
     */
    private function handleCreateExecute(SubmitButton $button) {
        $values = $button->getForm()->getValues();
        $this->storeDesignFormToSession($values);

        if ($this->isAjax()) {
            $this->invalidateControl('adhocResultsComponent');
        } else {
            $this->redirect('this', [self::PARAM_LOAD_FROM_SESSION => true]);
        }
    }

    /**
     * @param SubmitButton $button
     * @return void
     * @throws AbortException
     * @throws \ReflectionException
     */
    private function handleEditSuccess(SubmitButton $button) {
        try {
            $storedQuery = $this->getPatternQuery();
            if (!$this->getContestAuthorizator()->isAllowed($storedQuery, 'edit', $this->getSelectedContest())) {
                throw new ForbiddenRequestException('Nedostatečné oprávnění ke vytvoření dotazu.');
            }

            $form = $button->getForm();
            $values = $form->getValues();
            $this->handleSave($values, $storedQuery);

            $this->flashMessage(_('Dotaz upraven.'), self::FLASH_SUCCESS);
            $this->backLinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage(), self::FLASH_ERROR);
        } catch (ModelException $exception) {
            $this->flashMessage(_('Chyba při ukládání do databáze.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }
    }

    /**
     * @param SubmitButton $button
     * @return void
     * @throws AbortException
     * @throws \ReflectionException
     */
    private function handleCreateSuccess(SubmitButton $button) {
        try {
            if (!$this->getContestAuthorizator()->isAllowed('storedQuery', 'create', $this->getSelectedContest())) {
                throw new ForbiddenRequestException('Nedostatečné oprávnění ke vytvoření dotazu.');
            }

            $form = $button->getForm();
            $values = $form->getValues();
            /** @var ModelStoredQuery $storedQuery */
            $storedQuery = $this->serviceStoredQuery->createNew();
            $this->handleSave($values, $storedQuery);

            $this->flashMessage(_('Dotaz vytvořen.'), self::FLASH_SUCCESS);
            $this->backLinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (BadRequestException $exception) {
            $this->flashMessage($exception->getMessage(), self::FLASH_ERROR);
        } catch (ModelException $exception) {
            $this->flashMessage(_('Chyba při ukládání do databáze.'), self::FLASH_ERROR);
            Debugger::log($exception);
        }
    }

    /**
     * @param $values
     * @param ModelStoredQuery $storedQuery
     * @return void
     */
    private function handleSave($values, $storedQuery) {
        $connection = $this->serviceStoredQuery->getConnection();
        $connection->beginTransaction();

        $metadata = $values[self::CONT_META];
        $metadata = FormUtils::emptyStrToNull($metadata);
        $this->serviceStoredQuery->updateModel($storedQuery, $metadata);

        $sqlData = $values[self::CONT_CONSOLE];
        $this->serviceStoredQuery->updateModel($storedQuery, $sqlData);

        $this->serviceStoredQuery->save($storedQuery);

        $this->serviceMStoredQueryTag->getJoinedService()->getTable()->where([
            'query_id' => $storedQuery->query_id,
        ])->delete();
        foreach ($metadata['tags'] as $tagTypeId) {
            $data = [
                'query_id' => $storedQuery->query_id,
                'tag_type_id' => $tagTypeId,
            ];
            $tag = $this->serviceMStoredQueryTag->createNew($data);
            $this->serviceMStoredQueryTag->save($tag);
        }

        $this->serviceStoredQueryParameter->getTable()
            ->where(['query_id' => $storedQuery->query_id])->delete();

        foreach ($values[self::CONT_PARAMS_META] as $paramMetaData) {
            /** @var ModelStoredQueryParameter $parameter */
            $parameter = $this->serviceStoredQueryParameter->createNew($paramMetaData);
            $parameter->setDefaultValue($paramMetaData['default']);

            $parameter->query_id = $storedQuery->query_id;
            $this->serviceStoredQueryParameter->save($parameter);
        }

        $this->clearSession();
        $connection->commit();
    }

    /**
     * @return ServiceStoredQuery
     */
    protected function getORMService() {
        return $this->serviceStoredQuery;
    }
}
