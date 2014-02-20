<?php

namespace OrgModule;

use FKSDB\Components\Controls\StoredQueryComponent;
use FKSDB\Components\Forms\Factories\StoredQueryFactory as StoredQueryFormFactory;
use FKSDB\Components\Grids\StoredQueriesGrid;
use FormUtils;
use IResultsModel;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use ModelContest;
use ModelException;
use ModelPerson;
use ModelPostContact;
use ModelStoredQuery;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use ServiceStoredQuery;
use ServiceStoredQueryParameter;
use SQL\StoredQuery;
use SQL\StoredQueryFactory;

class ExportPresenter extends SeriesPresenter {

    const CONT_CONSOLE = 'console';
    const CONT_PARAMS_META = 'paramsMeta';
    const CONT_META = 'meta';
    const SESSION_NS = 'sql';
    const PARAM_PREFIX = 'p-';
    const PARAM_LOAD_FROM_SESSION = 'lfs';

    /**
     * @persistent
     */
    public $lfs;

    /**
     * @var ServiceStoredQuery
     */
    private $serviceStoredQuery;

    /**
     * @var ServiceStoredQueryParameter
     */
    private $serviceStoredQueryParameter;

    /**
     * @var StoredQueryFormFactory
     */
    private $storedQueryFormFactory;

    /**
     * @var StoredQueryFactory
     */
    private $storedQueryFactory;
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

    public function injectStoredQueryFactory(StoredQueryFactory $storedQueryFactory) {
        $this->storedQueryFactory = $storedQueryFactory;
        $this->storedQueryFactory->setPresenter($this);
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
        $parameters = array();
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
        $this->setAuthorized($this->getContestAuthorizator()->isAllowed($query, 'show', $this->getSelectedContest()));
    }

    public function actionExecute($id) {
        $query = $this->getPatternQuery();
        if ($query && $this->getParameter('qid')) {
            $this->redirect('this', $query->getPrimary());
        }
        $storedQuery = $this->storedQueryFactory->createQuery($query);
        $this->setStoredQuery($storedQuery);
    }

    public function titleEdit($id) {
        $this->setTitle(sprintf(_('Úprava dotazu %s'), $this->getPatternQuery()->name));
    }

    public function renderEdit($id) {
        $query = $this->getPatternQuery();

        $values = $this->getDesignFormFromSession();
        if (!$values) {
            $values = array();
            $values[self::CONT_CONSOLE] = $this->getPatternQuery();
            $values[self::CONT_META] = $this->getPatternQuery();
            $values[self::CONT_PARAMS_META] = array();
            foreach ($query->getParameters() as $parameter) {
                $paramData = $parameter->toArray();
                $paramData['default'] = $parameter->getDefaultValue();
                $values[self::CONT_PARAMS_META][] = $paramData;
            }
        }

        $this['editForm']->setDefaults($values);
    }

    public function titleCompose() {
        $this->setTitle(sprintf(_('Napsat dotaz')));
    }

    public function renderCompose() {
        $query = $this->getPatternQuery();

        $values = $this->getDesignFormFromSession();
        if ($values) {
            $this['composeForm']->setDefaults($values);
        }
    }

    public function titleList() {
        $this->setTitle(_('Exporty'));
    }

    public function titleShow($id) {
        $this->setTitle(sprintf(_('Detail dotazu %s'), $this->getPatternQuery()->name));
    }

    public function renderShow($id) {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    public function titleExecute($id) {
        $this->setTitle(sprintf(_('%s'), $this->getPatternQuery()->name));
    }

    public function renderExecute($id) {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    protected function createComponentGrid($name) {
        $grid = new StoredQueriesGrid($this->serviceStoredQuery, $this->getContestAuthorizator());
        return $grid;
    }

    protected function createComponentAdhocResultsComponent($name) {
        $storedQuery = $this->getStoredQuery();
        $grid = new StoredQueryComponent($storedQuery, $this->getContestAuthorizator(), $this->storedQueryFormFactory);
        $grid->setShowParametrize(false);
        return $grid;
    }

    protected function createComponentResultsComponent($name) {
        $storedQuery = $this->getStoredQuery();
        $grid = new StoredQueryComponent($storedQuery, $this->getContestAuthorizator(), $this->storedQueryFormFactory);
        return $grid;
    }

    protected function createComponentComposeForm($name) {
        $form = $this->createDesignForm();
        $form->addSubmit('save', _('Uložit'))
                ->onClick[] = array($this, 'handleComposeSuccess');
        return $form;
    }

    protected function createComponentEditForm($name) {
        $form = $this->createDesignForm();
        $form->addSubmit('save', _('Uložit'))
                ->onClick[] = array($this, 'handleEditSuccess');
        return $form;
    }

    private function createDesignForm() {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

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

        return $form;
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
        $this->template->data = array();

        foreach ($model->getCategories() as $category) {
            $rows = array();
            $model->setSeries(array(1, 2, 3, 4, 5, 6));

            $header = $model->getDataColumns($category);
            $sumCol = 0;
            foreach ($header as $column) {
                if ($column[IResultsModel::COL_DEF_LABEL] == IResultsModel::LABEL_SUM) {
                    break;
                }
                $sumCol++;
            }

            $datas = array();
            foreach ($model->getData($category) as $data) {
                if ($data->sum !== null) {
                    $datas[] = $data;
                }
            }

            foreach ($datas as $data) {
                $ctid = $data->ct_id;

                $row = array();
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
