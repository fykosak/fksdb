<?php

namespace OrgModule;

use FKSDB\Components\Forms\Factories\StoredQueryFactory as StoredQueryFormFactory;
use FKSDB\Components\Grids\StoredQueriesGrid;
use FKSDB\Components\View\StoredQueryResult;
use FormUtils;
use IResultsModel;
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
    const CONT_PARAMS = 'params';
    const CONT_META = 'meta';
    const SESSION_NS = 'sql';

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
        return $this->storedQuery; //TODO
//        $section = $this->session->getSection(self::SESSION_NS);
//        if (isset($section->storedQuery)) {
//            return $section->storedQuery;
//        } else {
//            return null;
//        }
    }

    public function setStoredQuery(StoredQuery $storedQuery) {
        $this->storedQuery = $storedQuery; //TODO
//        $section = $this->session->getSection(self::SESSION_NS);
//        $section->storedQuery = $storedQuery;
    }

    public function getPatternQuery() {
        if ($this->patternQuery === false) {
            $id = $this->getParam('id');
            $this->patternQuery = $this->serviceStoredQuery->findByPrimary($id);
            if (!$this->patternQuery && $this->getParam('qid')) {
                $this->patternQuery = $this->serviceStoredQuery->find(array(
                    'qid' => $this->getParam('qid')
                ));
            }
        }
        return $this->patternQuery;
    }

    public function canParametrize() {
        $query = $this->getPatternQuery();
        return count($query->getParameters()) && $this->getContestAuthorizator()->isAllowed($query, 'parametrize', $this->getSelectedContest());
    }

    public function actionList() {
        if (!$this->getContestAuthorizator()->isAllowed('query.stored', 'search', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function actionCompose() {
        if (!($this->getContestAuthorizator()->isAllowed('query.stored', 'create', $this->getSelectedContest()) ||
                $this->getContestAuthorizator()->isAllowed('query.adhoc', 'execute', $this->getSelectedContest()))) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function actionEdit($id) {
        $query = $this->getPatternQuery();
        if (!$this->getContestAuthorizator()->isAllowed($query, 'edit', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function actionShow($id) {
        $query = $this->getPatternQuery();
        if (!$this->getContestAuthorizator()->isAllowed($query, 'read', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }
    }

    public function actionExecute($id) {
        $query = $this->getPatternQuery();
        if (!$this->getContestAuthorizator()->isAllowed($query, 'execute', $this->getSelectedContest())) {
            throw new BadRequestException('Nedostatečné oprávnění.', 403);
        }

        $storedQuery = $this->storedQueryFactory->createQuery($query);
        $this->setStoredQuery($storedQuery);
    }

    public function renderEdit($id) {
        $query = $this->getPatternQuery();

        $values = array();
        $values[self::CONT_CONSOLE] = $this->getPatternQuery();
        $values[self::CONT_META] = $this->getPatternQuery();
        $values[self::CONT_PARAMS_META] = array();
        foreach ($query->getParameters() as $parameter) {
            $paramData = $parameter->toArray();
            $paramData['default'] = $parameter->getDefaultValue();
            $values[self::CONT_PARAMS_META][] = $paramData;
        }

        $this['editForm']->setDefaults($values);
    }

    public function renderShow($id) {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    public function renderExecute($id) {
        $this->template->storedQuery = $this->getPatternQuery();
    }

    protected function createComponentGrid($name) {
        $grid = new StoredQueriesGrid($this->serviceStoredQuery, $this->getContestAuthorizator());
        return $grid;
    }

    protected function createComponentResultsComponent($name) {
        $storedQuery = $this->getStoredQuery();
        $grid = new StoredQueryResult($storedQuery);
        return $grid;
    }

    protected function createComponentComposeForm($name) {
        $form = $this->createDesignForm();
        $form->addSubmit('save', 'Uložit')
                ->onClick[] = array($this, 'handleComposeSuccess');
        return $form;
    }

    protected function createComponentEditForm($name) {
        $form = $this->createDesignForm();
        $form->addSubmit('save', 'Uložit')
                ->onClick[] = array($this, 'handleEditSuccess');
        return $form;
    }

    private function createDesignForm() {
        $form = new Form();

        $group = $form->addGroup('SQL');

        $console = $this->storedQueryFormFactory->createConsole(0, $group);
        $form->addComponent($console, self::CONT_CONSOLE);

        $params = $this->storedQueryFormFactory->createParametersMetadata(0, $group);
        $form->addComponent($params, self::CONT_PARAMS_META);


        $group = $form->addGroup('Metadata');

        $metadata = $this->storedQueryFormFactory->createMetadata(0, $group);
        $form->addComponent($metadata, self::CONT_META);

        $form->setCurrentGroup();

        $form->addSubmit('execute', 'Spustit')
                        ->setValidationScope(false)
                ->onClick[] = array($this, 'handleComposeExecute');

        return $form;
    }

    protected function createComponentParametrizeForm($name) {
        $form = new Form();

        $queryPattern = $this->getPatternQuery();
        $parameters = $this->storedQueryFormFactory->createParametersValues($queryPattern);
        $form->addComponent($parameters, self::CONT_PARAMS);

        $form->addSubmit('execute', 'Spustit');
        $form->onSuccess[] = array($this, 'handleParametrize');

        return $form;
    }

    public function handleComposeExecute(SubmitButton $button) {
        if (!$this->getContestAuthorizator()->isAllowed('query.adhoc', 'execute', $this->getSelectedContest())) {
            $this->flashMessage('Nedostatečné oprávnění ke spuštění dotazu.', 'error');
            return;
        }

        $form = $button->getForm();

        $values = $form->getValues();

        $sql = $values[self::CONT_CONSOLE]['sql'];
        $parameters = array();
        foreach ($values[self::CONT_PARAMS_META] as $paramMetaData) {
            $parameter = $this->serviceStoredQueryParameter->createNew($paramMetaData);
            $parameter->setDefaultValue($paramMetaData['default']);
            $parameters[] = $parameter;
        }

        $storedQuery = $this->storedQueryFactory->createQueryFromSQL($sql, $parameters);
        $this->setStoredQuery($storedQuery);
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

            $this->flashMessage('Dotaz upraven.');
            $this->redirect('list');
        } catch (BadRequestException $e) {
            $this->flashMessage($e->getMessage(), 'error');
        } catch (ModelException $e) {
            $this->flashMessage('Chyba při ukládání do databáze.', 'error');
            Debugger::log($e);
        }
    }

    public function handleComposeSuccess(SubmitButton $button) {
        try {
            if (!$this->getContestAuthorizator()->isAllowed('query.stored', 'create', $this->getSelectedContest())) {
                throw new BadRequestException('Nedostatečné oprávnění ke vytvoření dotazu.', 403);
            }

            $form = $button->getForm();
            $values = $form->getValues();
            $storedQuery = $this->serviceStoredQuery->createNew();
            $this->handleSave($values, $storedQuery);


            $this->flashMessage('Dotaz vytvořen.');
            $this->redirect('list');
        } catch (BadRequestException $e) {
            $this->flashMessage($e->getMessage(), 'error');
        } catch (ModelException $e) {
            $this->flashMessage('Chyba při ukládání do databáze.', 'error');
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

        $connection->commit();
    }

    public function handleParametrize(Form $form) {
        try {
            if (!$this->canParametrize()) {
                throw new BadRequestException('Nedostatečné oprávnění k parametrizování dotazu.', 403);
            }

            $storedQuery = $this->getStoredQuery();
            $parameters = array();
            $values = $form->getValues();
            foreach ($values[self::CONT_PARAMS] as $key => $values) {
                $parameters[$key] = $values['value'];
            }
            $storedQuery->setParameters($parameters);
        } catch (BadRequestException $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
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
                $contestant = $serviceContestant->findByPrimary($ctid);
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
