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
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\ModelPostContact;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\ORM\Services\ServiceContestant;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryParameter;
use FKSDB\Results\Models\AbstractResultsModel;
use FKSDB\Results\ResultsModelFactory;
use FormUtils;
use ModelException;
use Nette\Application\BadRequestException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Strings;
use ServiceMStoredQueryTag;
use Tracy\Debugger;
use Traversable;

/**
 * Class ExportPresenter
 * @package OrgModule
 */
class ExportPresenter extends SeriesPresenter {

    const CONT_CONSOLE = 'console';
    const CONT_PARAMS_META = 'paramsMeta';
    const CONT_META = 'meta';
    const SESSION_NS = 'sql';
    const PARAM_PREFIX = 'p-';
    const PARAM_LOAD_FROM_SESSION = 'lfs';
    const PARAM_HTTP_AUTH = 'ha';

    /**
     * @persistent
     */
    public $lfs;

    /**
     * @persistent
     */
    public $qid;

    /**
     * @var \FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @var \FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryParameter
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
     * @var ExportFormatFactory
     */
    private $exportFormatFactory;
    /**
     * @var
     */
    private $storedQuery;

    /**
     * @var \FKSDB\ORM\Models\StoredQuery\ModelStoredQuery
     */
    private $patternQuery = false;

    /**
     * @param \FKSDB\ORM\Services\StoredQuery\ServiceStoredQuery $serviceStoredQuery
     */
    public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery) {
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    /**
     * @param StoredQueryFormFactory $storedQueryFormFactory
     */
    public function injectStoredQueryFormFactory(StoredQueryFormFactory $storedQueryFormFactory) {
        $this->storedQueryFormFactory = $storedQueryFormFactory;
    }

    /**
     * @param ServiceStoredQueryParameter $serviceStoredQueryParameter
     */
    public function injectServiceStoredQueryParameter(ServiceStoredQueryParameter $serviceStoredQueryParameter) {
        $this->serviceStoredQueryParameter = $serviceStoredQueryParameter;
    }

    /**
     * @param ServiceMStoredQueryTag $serviceMStoredQueryTag
     */
    public function injectServiceMStoredQueryTag(ServiceMStoredQueryTag $serviceMStoredQueryTag) {
        $this->serviceMStoredQueryTag = $serviceMStoredQueryTag;
    }

    /**
     * @param StoredQueryFactory $storedQueryFactory
     */
    public function injectStoredQueryFactory(StoredQueryFactory $storedQueryFactory) {
        $this->storedQueryFactory = $storedQueryFactory;
        $this->storedQueryFactory->setPresenter($this);
    }

    /**
     * @param ExportFormatFactory $exportFormatFactory
     */
    public function injectExportFormatFactory(ExportFormatFactory $exportFormatFactory) {
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

    /**
     * @param StoredQuery $storedQuery
     */
    public function setStoredQuery(StoredQuery $storedQuery) {
        $this->storedQuery = $storedQuery; //TODO
    }

    /**
     * @param $values
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
            /**
             * @var ModelStoredQueryParameter $parameter
             */
            $parameter = $this->serviceStoredQueryParameter->createNew($paramMetaData);
            $parameter->setDefaultValue($paramMetaData['default']);
            $parameters[] = $parameter;
        }

        return $this->storedQueryFactory->createQueryFromSQL($sql, $parameters);
    }

    /**
     * @return ModelStoredQuery|\Nette\Database\Table\ActiveRow|null
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

    /**
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('storedQuery', 'list', $this->getSelectedContest()));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedCompose() {
        $this->setAuthorized(
            ($this->getContestAuthorizator()->isAllowed('storedQuery', 'create', $this->getSelectedContest()) &&
                $this->getContestAuthorizator()->isAllowed('export.adhoc', 'execute', $this->getSelectedContest()))
        );
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedEdit($id) {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new BadRequestException('Neexistující dotaz.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($query, 'edit', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedShow($id) {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new BadRequestException('Neexistující dotaz.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($query, 'show', $this->getSelectedContest()));
    }

    /**
     * @param $id
     * @throws BadRequestException
     */
    public function authorizedExecute($id) {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new BadRequestException('Neexistující dotaz.', 404);
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

    /**
     * @return string
     */
    protected function getHttpRealm() {
        return 'FKSDB-export';
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function actionExecute($id) {
        $query = $this->getPatternQuery();
        $storedQuery = $this->storedQueryFactory->createQuery($query);
        $this->setStoredQuery($storedQuery);

        if ($query && $this->getParameter('qid')) {
            $parameters = [];
            foreach ($this->getParameter() as $key => $value) {
                if (Strings::startsWith($key, StoredQueryComponent::PARAMETER_URL_PREFIX)) {
                    $parameters[substr($key, strlen(StoredQueryComponent::PARAMETER_URL_PREFIX))] = $value;
                }
            }
            $storedQueryComponent = $this->getComponent('resultsComponent');
            $storedQueryComponent->updateParameters($parameters);

            if ($this->getParameter('format')) {
                $this->createRequest($storedQueryComponent, 'format!', array('format' => $this->getParameter('format')), 'forward');
                $this->forward($this->lastCreatedRequest);
            }
        }
    }

    /**
     * @param $id
     */
    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava dotazu %s'), $this->getPatternQuery()->name));
        $this->setIcon('fa fa-pencil');
    }

    /**
     * @param $id
     */
    public function renderEdit($id) {
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

    public function titleCompose() {
        $this->setTitle(sprintf(_('Napsat dotaz')));
        $this->setIcon('fa fa-pencil');
    }

    public function renderCompose() {
        $values = $this->getDesignFormFromSession();
        if ($values) {
            $this->getComponent('composeForm')->getForm()->setDefaults($values);
        }
    }

    public function titleList() {
        $this->setTitle(_('Exporty'));
        $this->setIcon('fa fa-database');
    }

    /**
     * @param $id
     */
    public function titleShow($id) {
        $title = sprintf(_('Detail dotazu %s'), $this->getPatternQuery()->name);
        $qid = $this->getPatternQuery()->qid;
        if ($qid) { // intentionally =
            $title .= " ($qid)";
        }

        $this->setTitle($title);
        $this->setIcon('fa fa-database');
    }

    /**
     * @param $id
     */
    public function renderShow($id) {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    /**
     * @param $id
     */
    public function titleExecute($id) {
        $this->setTitle(sprintf(_('%s'), $this->getPatternQuery()->name));
        $this->setIcon('fa fa-play-circle-o');
    }

    /**
     * @param $id
     */
    public function renderExecute($id) {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    /**
     * @return ContestChooser
     */
    protected function createComponentContestChooser(): ContestChooser {
        $component = parent::createComponentContestChooser();
        if ($this->getAction() == 'execute') {
            // Contest and year check is done in StoredQueryComponent
            $component->setContests(ContestChooser::CONTESTS_ALL);
            $component->setYears(ContestChooser::YEARS_ALL);
        }
        return $component;
    }

    /**
     * @return StoredQueriesGrid
     */
    protected function createComponentGrid(): StoredQueriesGrid {
        return new StoredQueriesGrid($this->serviceStoredQuery, $this->getContestAuthorizator(), $this->tableReflectionFactory);
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
        $grid = new StoredQueryComponent($storedQuery, $this->getContestAuthorizator(), $this->storedQueryFormFactory, $this->exportFormatFactory);
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
        return new StoredQueryComponent($storedQuery, $this->getContestAuthorizator(), $this->storedQueryFormFactory, $this->exportFormatFactory);
    }

    /**
     * @return StoredQueryTagCloud
     */
    protected function createComponentTagCloudList(): StoredQueryTagCloud {
        $tagCloud = new StoredQueryTagCloud(StoredQueryTagCloud::MODE_LIST, $this->serviceMStoredQueryTag);
        $tagCloud->registerOnClick($this->getComponent('grid')->getFilterByTagCallback());
        return $tagCloud;
    }

    /**
     * @return StoredQueryTagCloud
     */
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
            ->onClick[] = [$this, 'handleComposeSuccess'];
        return $control;
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentEditForm(): FormControl {
        $control = $this->createDesignForm();
        $control->getForm()->addSubmit('save', _('Save'))
            ->onClick[] = [$this, 'handleEditSuccess'];
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

        $submit = $form->addSubmit('execute', _('Spustit'))
            ->setValidationScope(false);
        $submit->getControlPrototype()->addClass('btn-success');
        $submit->onClick[] = array($this, 'handleComposeExecute');

        return $control;
    }

    /**
     * @param SubmitButton $button
     * @throws \Nette\Application\AbortException
     */
    public function handleComposeExecute(SubmitButton $button) {
        $form = $button->getForm();
        $values = $form->getValues();
        $this->storeDesignFormToSession($values);

        if ($this->isAjax()) {
            $this->invalidateControl('adhocResultsComponent');
        } else {
            $this->redirect('this', array(self::PARAM_LOAD_FROM_SESSION => true));
        }
    }

    /**
     * @param SubmitButton $button
     * @throws \Nette\Application\AbortException
     * @throws \ReflectionException
     */
    public function handleEditSuccess(SubmitButton $button) {
        try {
            $storedQuery = $this->getPatternQuery();
            if (!$this->getContestAuthorizator()->isAllowed($storedQuery, 'edit', $this->getSelectedContest())) {
                throw new BadRequestException('Nedostatečné oprávnění ke vytvoření dotazu.', 403);
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
     * @throws \Nette\Application\AbortException
     * @throws \ReflectionException
     */
    public function handleComposeSuccess(SubmitButton $button) {
        try {
            if (!$this->getContestAuthorizator()->isAllowed('storedQuery', 'create', $this->getSelectedContest())) {
                throw new BadRequestException('Nedostatečné oprávnění ke vytvoření dotazu.', 403);
            }

            $form = $button->getForm();
            $values = $form->getValues();
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
    private function handleSave($values, $storedQuery) {
        $connection = $this->serviceStoredQuery->getConnection();
        $connection->beginTransaction();

        $metadata = $values[self::CONT_META];
        $metadata = FormUtils::emptyStrToNull($metadata);
        // $this->serviceStoredQuery->updateModel($storedQuery, $metadata);

        $sqlData = $values[self::CONT_CONSOLE];
        //  $this->serviceStoredQuery->updateModel($storedQuery, $sqlData);
        $this->serviceStoredQuery->updateModel2($storedQuery, array_merge($metadata, $sqlData));

        // $this->serviceStoredQuery->save($storedQuery);

        $this->serviceMStoredQueryTag->getJoinedService()->getTable()->where([
            'query_id' => $storedQuery->query_id,
        ])->delete();
        foreach ($metadata['tags'] as $tagTypeId) {
            $data = array(
                'query_id' => $storedQuery->query_id,
                'tag_type_id' => $tagTypeId,
            );
            //TODO
            $tag = $this->serviceMStoredQueryTag->createNew($data);
            $this->serviceMStoredQueryTag->save($tag);
        }

        $this->serviceStoredQueryParameter->getTable()
            ->where(array('query_id' => $storedQuery->query_id))->delete();

        foreach ($values[self::CONT_PARAMS_META] as $paramMetaData) {
            /**
             * @var ModelStoredQueryParameter $parameter
             */
            $paramMetaData['query_id'] = $storedQuery->query_id;
            $parameter = $this->serviceStoredQueryParameter->createNewModel($paramMetaData);
            $parameter->setDefaultValue($paramMetaData['default']);

            //$parameter->query_id = $storedQuery->query_id;
            //$this->serviceStoredQueryParameter->save($parameter);
        }

        $this->clearSession();
        $connection->commit();
    }

    /**
     * Very ineffective solution that provides data in
     * specified format.
     *
     * @throws BadRequestException
     * @deprecated
     */
    public function renderOvvp() {
        /**
         * @var ResultsModelFactory $modelFactory
         */
        $modelFactory = $this->getService('resultsModelFactory');
        $serviceContestant = $this->getService(ServiceContestant::class);


        $model = $modelFactory->createCumulativeResultsModel($this->getSelectedContest(), $this->getSelectedYear());
        $this->template->data = [];

        foreach ($model->getCategories() as $category) {
            $rows = [];
            $model->setSeries(array(1, 2, 3, 4, 5, 6));

            $header = $model->getDataColumns($category);
            $sumCol = 0;
            foreach ($header as $column) {
                if ($column[AbstractResultsModel::COL_DEF_LABEL] == AbstractResultsModel::LABEL_SUM) {
                    break;
                }
                $sumCol++;
            }

            $datas = [];
            foreach ($model->getData($category) as $data) {
                if ($data->sum !== null) {
                    $datas[] = $data;
                }
            }

            foreach ($datas as $data) {
                $ctid = $data->ct_id;

                $row = [];
                //TODO unechecked
                $contestant = $serviceContestant->getTable()->getConnection()->table(DbNames::VIEW_CONTESTANT)->where('ct_id', $ctid);
                $person = ModelPerson::createFromActiveRow($contestant->person);

                // jména
                $row[] = $person->other_name;
                $row[] = $person->family_name;

                // adresa dom
                $contacts = $person->getPostContacts();
                $bestMatch = null;
                foreach ($contacts as $contact) {
                    if ($contact->type == 'D') {
                        $bestMatch = $contact;
                        break;
                    } else {
                        $bestMatch = $contact;
                    }
                }
                if ($bestMatch) {
                    $bestMatch = ModelPostContact::createFromActiveRow($bestMatch);
                    $address = $bestMatch->getAddress();
                    $parts = explode(' ', $address->target);

                    $row[] = implode(' ', array_slice($parts, 0, count($parts) - 1));
                    $row[] = $parts[count($parts) - 1];
                    $row[] = $address->city;
                    $row[] = $address->postal_code;
                    $row[] = ($address->region->country_iso == 'EP') ? '' : strtolower($address->region->country_iso);
                } else {
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                    $row[] = '';
                }

                // škola
                if ($contestant->school) {
                    $row[] = $contestant->school->name_abbrev;
                    $row[] = $contestant->school->izo;
                } else {
                    $row[] = '';
                    $row[] = '';
                }

                // rok maturity
                if ($contestant->study_year !== null) {
                    $year = $this->getSelectedYear();
                    $studyYear = ($contestant->study_year >= 1 && $contestant->study_year <= 4) ? $contestant->study_year : ($contestant->study_year - 9);
                    if ($contestant->contest_id == ModelContest::ID_FYKOS) {
                        $row[] = 1991 + $year - $studyYear;
                    } else if ($contestant->contest_id == ModelContest::ID_VYFUK) {
                        $row[] = 2015 + $year - $studyYear;
                    }
                } else {
                    $row[] = '';
                }

                // e-mail
                if ($person->getLogin() && $person->getLogin()->email) {
                    $row[] = $person->getLogin()->email;
                } else {
                    $row[] = '';
                }

                // pořadí
                $row[] = (($data->from == $data->to) ? $data->from : ($data->from . '-' . $data->to)) . '/' . count($datas);

                // body
                $row[] = $data->sum . '/' . $header[$sumCol][AbstractResultsModel::COL_DEF_LIMIT];


                // append
                if ($data->sum !== null) {
                    $rows[] = $row;
                }
            }
            $this->template->data[$category->id] = $rows;
        }
    }

}
