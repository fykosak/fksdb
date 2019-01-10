<?php

namespace OrgModule;

use AuthenticatedPresenter;
use DbNames;
use Exports\ExportFormatFactory;
use Exports\StoredQuery;
use Exports\StoredQueryFactory;
use FKSDB\Components\Controls\ContestChooser;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\StoredQueryComponent;
use FKSDB\Components\Controls\StoredQueryTagCloud;
use FKSDB\Components\Forms\Factories\StoredQueryFactory as StoredQueryFormFactory;
use FKSDB\Components\Grids\StoredQueriesGrid;
use FKSDB\ORM\ModelContest;
use FKSDB\ORM\ModelPerson;
use FKSDB\ORM\ModelPostContact;
use FKSDB\ORM\ModelStoredQuery;
use FormUtils;
use IResultsModel;
use ModelException;
use Nette\Application\BadRequestException;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Strings;
use ServiceMStoredQueryTag;
use ServiceStoredQuery;
use ServiceStoredQueryParameter;

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
     * @var ExportFormatFactory
     */
    private $exportFormatFactory;
    private $storedQuery;

    /**
     * @var ModelStoredQuery
     */
    private $patternQuery = false;

    public function injectServiceStoredQuery(ServiceStoredQuery $serviceStoredQuery) {
        $this->serviceStoredQuery = $serviceStoredQuery;
    }

    public function injectStoredQueryFormFactory(StoredQueryFormFactory $storedQueryFormFactory) {
        $this->storedQueryFormFactory = $storedQueryFormFactory;
    }

    public function injectServiceStoredQueryParameter(ServiceStoredQueryParameter $serviceStoredQueryParameter) {
        $this->serviceStoredQueryParameter = $serviceStoredQueryParameter;
    }

    public function injectServiceMStoredQueryTag(ServiceMStoredQueryTag $serviceMStoredQueryTag) {
        $this->serviceMStoredQueryTag = $serviceMStoredQueryTag;
    }

    public function injectStoredQueryFactory(StoredQueryFactory $storedQueryFactory) {
        $this->storedQueryFactory = $storedQueryFactory;
        $this->storedQueryFactory->setPresenter($this);
    }

    public function injectExportFormatFactory(ExportFormatFactory $exportFormatFactory) {
        $this->exportFormatFactory = $exportFormatFactory;
    }

    /**
     * @return StoredQuery
     */
    public function getStoredQuery() {
        if ($this->storedQuery) {
            return $this->storedQuery;
        } else {
            return $this->getStoredQueryFromSession();
        }
    }

    public function setStoredQuery(StoredQuery $storedQuery) {
        $this->storedQuery = $storedQuery; //TODO
    }

    private function storeDesignFormToSession($values) {
        $section = $this->session->getSection(self::SESSION_NS);
        $section->data = $values;
    }

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

    protected function getStoredQueryFromSession() {
        $data = $this->getDesignFormFromSession();
        if (!$data) {
            return null;
        }

        $sql = $data[self::CONT_CONSOLE]['sql'];
        $parameters = [];
        foreach ($data[self::CONT_PARAMS_META] as $paramMetaData) {
            $parameter = $this->serviceStoredQueryParameter->createNew($paramMetaData);
            $parameter->setDefaultValue($paramMetaData['default']);
            $parameters[] = $parameter;
        }

        return $this->storedQueryFactory->createQueryFromSQL($sql, $parameters);
    }

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

    public function authorizedList() {
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed('storedQuery', 'list', $this->getSelectedContest()));
    }

    public function authorizedCompose() {
        $this->setAuthorized(
            ($this->getContestAuthorizator()->isAllowed('storedQuery', 'create', $this->getSelectedContest()) &&
                $this->getContestAuthorizator()->isAllowed('export.adhoc', 'execute', $this->getSelectedContest()))
        );
    }

    public function authorizedEdit($id) {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new BadRequestException('Neexistující dotaz.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($query, 'edit', $this->getSelectedContest()));
    }

    public function authorizedShow($id) {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new BadRequestException('Neexistující dotaz.', 404);
        }
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($query, 'show', $this->getSelectedContest()));
    }

    public function authorizedExecute($id) {
        $query = $this->getPatternQuery();
        if (!$query) {
            throw new BadRequestException('Neexistující dotaz.', 404);
        }
        // proper authorization is done in StoredQueryComponent
    }

    public function getAllowedAuthMethods() {
        $methods = parent::getAllowedAuthMethods();
        if ($this->getParameter(self::PARAM_HTTP_AUTH, false)) {
            $methods = $methods | AuthenticatedPresenter::AUTH_ALLOW_HTTP;
        }
        return $methods;
    }

    protected function getHttpRealm() {
        return 'FKSDB-export';
    }

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

    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava dotazu %s'), $this->getPatternQuery()->name));
        $this->setIcon('fa fa-pencil');
    }

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

    public function titleShow($id) {
        $title = sprintf(_('Detail dotazu %s'), $this->getPatternQuery()->name);
        if ($qid = $this->getPatternQuery()->qid) { // intentionally =
            $title .= " ($qid)";
        }

        $this->setTitle($title);
        $this->setIcon('fa fa-database');
    }

    public function renderShow($id) {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    public function titleExecute($id) {
        $this->setTitle(sprintf(_('%s'), $this->getPatternQuery()->name));
        $this->setIcon('fa fa-play-circle-o');
    }

    public function renderExecute($id) {
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

    protected function createComponentGrid($name) {
        $grid = new StoredQueriesGrid($this->serviceStoredQuery, $this->getContestAuthorizator());
        return $grid;
    }

    protected function createComponentAdhocResultsComponent($name) {
        $storedQuery = $this->getStoredQuery();
        if ($storedQuery === null) { // workaround when session expires and persistent parameters from component are to be stored (because of redirect)
            return null;
        }
        $grid = new StoredQueryComponent($storedQuery, $this->getContestAuthorizator(), $this->storedQueryFormFactory, $this->exportFormatFactory);
        $grid->setShowParametrize(false);
        return $grid;
    }

    protected function createComponentResultsComponent($name) {
        $storedQuery = $this->getStoredQuery();
        if ($storedQuery === null) { // workaround when session expires and persistent parameters from component are to be stored (because of redirect)
            return null;
        }
        $grid = new StoredQueryComponent($storedQuery, $this->getContestAuthorizator(), $this->storedQueryFormFactory, $this->exportFormatFactory);
        return $grid;
    }

    protected function createComponentTagCloudList($name) {
        $tagCloud = new StoredQueryTagCloud(StoredQueryTagCloud::MODE_LIST, $this->serviceMStoredQueryTag);
        $tagCloud->registerOnClick($this->getComponent('grid')->getFilterByTagCallback());
        return $tagCloud;
    }

    protected function createComponentTagCloudDetail($name) {
        $tagCloud = new StoredQueryTagCloud(StoredQueryTagCloud::MODE_DETAIL, $this->serviceMStoredQueryTag);
        $tagCloud->setModelStoredQuery($this->getPatternQuery());
        return $tagCloud;
    }

    protected function createComponentComposeForm($name) {
        $control = $this->createDesignForm();
        $control->getForm()->addSubmit('save', _('Uložit'))
            ->onClick[] = [$this, 'handleComposeSuccess'];
        return $control;
    }

    protected function createComponentEditForm($name) {
        $control = $this->createDesignForm();
        $control->getForm()->addSubmit('save', _('Uložit'))
            ->onClick[] = [$this, 'handleEditSuccess'];
        return $control;
    }

    private function createDesignForm() {
        $control = new FormControl();
        $form = $control->getForm();

        $group = $form->addGroup(_('SQL'));

        $console = $this->storedQueryFormFactory->createConsole(0, $group);
        $form->addComponent($console, self::CONT_CONSOLE);

        $params = $this->storedQueryFormFactory->createParametersMetadata(0, $group);
        $form->addComponent($params, self::CONT_PARAMS_META);


        $group = $form->addGroup(_('Metadata'));

        $metadata = $this->storedQueryFormFactory->createMetadata(0, $group);
        $form->addComponent($metadata, self::CONT_META);

        $form->setCurrentGroup();

        $submit = $form->addSubmit('execute', _('Spustit'))
            ->setValidationScope(false);
        $submit->getControlPrototype()->addClass('btn-success');
        $submit->onClick[] = array($this, 'handleComposeExecute');

        return $control;
    }

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
            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (BadRequestException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
        } catch (ModelException $e) {
            $this->flashMessage(_('Chyba při ukládání do databáze.'), self::FLASH_ERROR);
            Debugger::log($e);
        }
    }

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
            $this->backlinkRedirect();
            $this->redirect('list'); // if there's no backlink
        } catch (BadRequestException $e) {
            $this->flashMessage($e->getMessage(), self::FLASH_ERROR);
        } catch (ModelException $e) {
            $this->flashMessage(_('Chyba při ukládání do databáze.'), self::FLASH_ERROR);
            Debugger::log($e);
        }
    }

    private function handleSave($values, $storedQuery) {
        $connection = $this->serviceStoredQuery->getConnection();
        $connection->beginTransaction();

        $metadata = $values[self::CONT_META];
        $metadata = FormUtils::emptyStrToNull($metadata);
        $this->serviceStoredQuery->updateModel($storedQuery, $metadata);

        $sqlData = $values[self::CONT_CONSOLE];
        $this->serviceStoredQuery->updateModel($storedQuery, $sqlData);

        $this->serviceStoredQuery->save($storedQuery);

        $this->serviceMStoredQueryTag->getJoinedService()->getTable()->where(array(
            'query_id' => $storedQuery->query_id,
        ))->delete();
        foreach ($metadata['tags'] as $tagTypeId) {
            $data = array(
                'query_id' => $storedQuery->query_id,
                'tag_type_id' => $tagTypeId,
            );
            $tag = $this->serviceMStoredQueryTag->createNew($data);
            $this->serviceMStoredQueryTag->save($tag);
        }

        $this->serviceStoredQueryParameter->getTable()
            ->where(array('query_id' => $storedQuery->query_id))->delete();

        foreach ($values[self::CONT_PARAMS_META] as $paramMetaData) {
            $parameter = $this->serviceStoredQueryParameter->createNew($paramMetaData);
            $parameter->setDefaultValue($paramMetaData['default']);

            $parameter->query_id = $storedQuery->query_id;
            $this->serviceStoredQueryParameter->save($parameter);
        }

        $this->clearSession();
        $connection->commit();
    }

    /**
     * Very ineffective solution that provides data in
     * specified format.
     *
     * @deprecated
     */
    public function renderOvvp() {
        $modelFactory = $this->getService('resultsModelFactory');
        $serviceContestant = $this->getService('ServiceContestant');


        $model = $modelFactory->createCumulativeResultsModel($this->getSelectedContest(), $this->getSelectedYear());
        $this->template->data = [];

        foreach ($model->getCategories() as $category) {
            $rows = [];
            $model->setSeries(array(1, 2, 3, 4, 5, 6));

            $header = $model->getDataColumns($category);
            $sumCol = 0;
            foreach ($header as $column) {
                if ($column[IResultsModel::COL_DEF_LABEL] == IResultsModel::LABEL_SUM) {
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
                $person = ModelPerson::createFromTableRow($contestant->person);

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
                    $bestMatch = ModelPostContact::createFromTableRow($bestMatch);
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
                $row[] = $data->sum . '/' . $header[$sumCol][IResultsModel::COL_DEF_LIMIT];


                // append
                if ($data->sum !== null) {
                    $rows[] = $row;
                }
            }
            $this->template->data[$category->id] = $rows;
        }
    }

}
