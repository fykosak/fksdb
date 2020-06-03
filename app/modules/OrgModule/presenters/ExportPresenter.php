<?php

namespace OrgModule;

use AuthenticatedPresenter;
use Exports\ExportFormatFactory;
use Exports\StoredQuery;
use Exports\StoredQueryFactory;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\StoredQueryComponent;
use FKSDB\Components\Controls\StoredQueryTagCloud;
use FKSDB\Components\Forms\Factories\StoredQueryFactory as StoredQueryFormFactory;
use FKSDB\Components\Grids\StoredQueriesGrid;
use FKSDB\CoreModule\SeriesPresenter\{ISeriesPresenter, SeriesPresenterTrait};
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
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Strings;
use FKSDB\ORM\ServicesMulti\ServiceMStoredQueryTag;
use Tracy\Debugger;
use Traversable;

/**
 * Class ExportPresenter
 * *
 */
class ExportPresenter extends BasePresenter implements ISeriesPresenter {
    use SeriesPresenterTrait;

    public const CONT_CONSOLE = 'console';
    public const CONT_PARAMS_META = 'paramsMeta';
    public const CONT_META = 'meta';
    public const SESSION_NS = 'sql';
    public const PARAM_LOAD_FROM_SESSION = 'lfs';
    public const PARAM_HTTP_AUTH = 'ha';

    /**
     * @persistent
     */
    public $qid;

    private ServiceStoredQuery $serviceStoredQuery;

    private ServiceStoredQueryParameter $serviceStoredQueryParameter;

    private ServiceMStoredQueryTag $serviceMStoredQueryTag;

    private StoredQueryFormFactory $storedQueryFormFactory;

    private StoredQueryFactory $storedQueryFactory;

    private ExportFormatFactory $exportFormatFactory;
    /**
     * @var StoredQuery
     */
    private $storedQuery;

    /**
     * @var ModelStoredQuery
     */
    private $patternQuery = false;

    public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery): void {
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    public function injectStoredQueryFormFactory(StoredQueryFormFactory $storedQueryFormFactory): void {
        $this->storedQueryFormFactory = $storedQueryFormFactory;
    }

    public function injectServiceStoredQueryParameter(ServiceStoredQueryParameter $serviceStoredQueryParameter): void {
        $this->serviceStoredQueryParameter = $serviceStoredQueryParameter;
    }

    public function injectServiceMStoredQueryTag(ServiceMStoredQueryTag $serviceMStoredQueryTag): void {
        $this->serviceMStoredQueryTag = $serviceMStoredQueryTag;
    }

    public function injectStoredQueryFactory(StoredQueryFactory $storedQueryFactory): void {
        $this->storedQueryFactory = $storedQueryFactory;
    }

    public function injectExportFormatFactory(ExportFormatFactory $exportFormatFactory): void {
        $this->exportFormatFactory = $exportFormatFactory;
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

    public function setStoredQuery(StoredQuery $storedQuery): void {
        $this->storedQuery = $storedQuery;
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
        if (!$this->getParam(self::PARAM_LOAD_FROM_SESSION, false)) {
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
     * @return ModelStoredQuery|ActiveRow|null
     */
    public function getPatternQuery() {
        if ($this->patternQuery === false) {
            $id = $this->getParam('id');
            $this->patternQuery = $this->serviceStoredQuery->findByPrimary($id);
            if (!$this->patternQuery && $this->getParam('qid')) {
                $this->patternQuery = $this->serviceStoredQuery->findByQid($this->getParam('qid'));
            }
        }
        return $this->patternQuery;
    }

    public function authorizedList(): void {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('storedQuery', 'list', $this->getSelectedContest()));
    }

    public function authorizedCompose(): void {
        $this->setAuthorized(
            ($this->getContestAuthorizator()->isAllowed('storedQuery', 'create', $this->getSelectedContest()) &&
                $this->getContestAuthorizator()->isAllowed('export.adhoc', 'execute', $this->getSelectedContest()))
        );
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedEdit($id): void {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new NotFoundException('Neexistující dotaz.');
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($query, 'edit', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedShow($id): void {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new NotFoundException('Neexistující dotaz.');
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($query, 'show', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedExecute($id): void {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new NotFoundException('Neexistující dotaz.');
        }
        // proper authorization is done in StoredQueryComponent
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

    protected function getHttpRealm(): ?string {
        return 'FKSDB-export';
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws AbortException
     * @throws InvalidLinkException
     */
    public function actionExecute($id): void {
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

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function titleEdit($id): void {
        $this->setTitle(sprintf(_('Úprava dotazu %s'), $this->getPatternQuery()->name), 'fa fa-pencil');
    }

    /**
     * @param mixed $id
     */
    public function renderEdit($id): void {
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

        $this->getComponent('editForm')->getForm()->setDefaults($values);
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleCompose(): void {
        $this->setTitle(sprintf(_('Napsat dotaz')), 'fa fa-pencil');
    }

    public function renderCompose(): void {
        $values = $this->getDesignFormFromSession();
        if ($values) {
            $this->getComponent('composeForm')->getForm()->setDefaults($values);
        }
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList(): void {
        $this->setTitle(_('Exports'), 'fa fa-database');
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function titleShow($id): void {
        $title = sprintf(_('Detail dotazu %s'), $this->getPatternQuery()->name);
        $qid = $this->getPatternQuery()->qid;
        if ($qid) {
            $title .= " ($qid)";
        }

        $this->setTitle($title, 'fa fa-database');
    }

    /**
     * @param mixed $id
     */
    public function renderShow($id): void {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function titleExecute($id): void {
        $this->setTitle(sprintf(_('%s'), $this->getPatternQuery()->name), 'fa fa-play-circle-o');
    }

    /**
     * @param mixed $id
     */
    public function renderExecute($id): void {
        $this->template->storedQuery = $this->getPatternQuery();
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
        $grid = new StoredQueryComponent($storedQuery, $this->getContestAuthorizator(), $this->storedQueryFormFactory, $this->exportFormatFactory, $this->getContext());
        $grid->setShowParametrize(false);
        return $grid;
    }

    /**
     * @return StoredQueryComponent|null
     * @throws BadRequestException
     */
    protected function createComponentResultsComponent() {
        $storedQuery = $this->getStoredQuery();
        if ($storedQuery === null) { // workaround when session expires and persistent parameters from component are to be stored (because of redirect)
            return null;
        }
        return new StoredQueryComponent($storedQuery, $this->getContestAuthorizator(), $this->storedQueryFormFactory, $this->exportFormatFactory, $this->getContext());
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
    protected function createComponentComposeForm(): FormControl {
        $control = $this->createDesignForm();
        $control->getForm()->addSubmit('save', _('Save'))
            ->onClick[] = function (SubmitButton $button) {
            $this->handleComposeSuccess($button);
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
        $control = new FormControl($this->getContext());
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

        $submit = $form->addSubmit('execute', _('Spustit'))
            ->setValidationScope(false);
        $submit->getControlPrototype()->addClass('btn-success');
        $submit->onClick[] = function (SubmitButton $button) {
            $this->handleComposeExecute($button);
        };

        return $control;
    }

    /**
     * @param SubmitButton $button
     * @throws AbortException
     */
    private function handleComposeExecute(SubmitButton $button): void {
        $form = $button->getForm();
        $values = $form->getValues();
        $this->storeDesignFormToSession($values);

        if ($this->isAjax()) {
            $this->invalidateControl('adhocResultsComponent');
        } else {
            $this->redirect('this', [self::PARAM_LOAD_FROM_SESSION => true]);
        }
    }

    /**
     * @param SubmitButton $button
     * @throws AbortException
     * @throws \ReflectionException
     */
    private function handleEditSuccess(SubmitButton $button): void {
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
     * @throws AbortException
     * @throws \ReflectionException
     */
    private function handleComposeSuccess(SubmitButton $button): void {
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
     * @param array|Traversable $values
     * @param ModelStoredQuery $storedQuery
     */
    private function handleSave($values, $storedQuery): void {
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
     * @param string $title
     * @param string $icon
     * @param string $subTitle
     * @throws BadRequestException
     */
    protected function setTitle(string $title, string $icon = '', string $subTitle = ''): void {
        parent::setTitle($title, $icon, $subTitle . ' ' . sprintf(_('%d. series'), $this->getSelectedSeries()));
    }
}
