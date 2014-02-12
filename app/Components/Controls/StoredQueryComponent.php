<?php

namespace FKSDB\Components\Controls;

use Authorization\ContestAuthorizator;
use FKSDB\Components\Forms\Factories\StoredQueryFactory;
use FKSDB\Components\Grids\StoredQueryGrid;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use PDOException;
use PePa\CSVResponse;
use SQL\StoredQuery;
use SQL\StoredQueryFactory as StoredQueryFactorySQL;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryComponent extends Control {

    const CONT_PARAMS = 'params';

    /**
     * @persistent
     * @var array
     */
    public $parameters;

    /**
     * @var StoredQuery
     */
    private $storedQuery;

    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;

    /**
     * @var StoredQueryFactory
     */
    private $storedQueryFormFactory;

    /**
     * @var null|bool|string
     */
    private $error;

    /**
     * @var bool
     */
    private $showParametrize = true;

    function __construct(StoredQuery $storedQuery, ContestAuthorizator $contestAuthorizator, StoredQueryFactory $storedQueryFormFactory) {
        $this->storedQuery = $storedQuery;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->storedQueryFormFactory = $storedQueryFormFactory;
    }

    public function getShowParametrize() {
        return $this->showParametrize;
    }

    public function setShowParametrize($showParametrize) {
        $this->showParametrize = $showParametrize;
    }

    protected function createComponentGrid($name) {
        $grid = new StoredQueryGrid($this->storedQuery);
        return $grid;
    }

    protected function createComponentParametrizeForm($name) {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        $queryPattern = $this->storedQuery->getQueryPattern();
        $parameters = $this->storedQueryFormFactory->createParametersValues($queryPattern);
        $form->addComponent($parameters, self::CONT_PARAMS);

        $form->addSubmit('execute', _('Parametrizovat'));
        $form->onSuccess[] = function(Form $form) {
                    $this->parameters = array();
                    $values = $form->getValues();
                    foreach ($values[self::CONT_PARAMS] as $key => $values) {
                        $this->parameters[$key] = $values['value'];
                    }
                };

        return $form;
    }

    public function getSqlError() {
        if ($this->error === null) {
            $this->error = false;
            try {
                $this->storedQuery->getColumnNames(); // this may throw PDOException in the main query
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
            }
        }
        return $this->error;
    }

    public function render() {
        if ($this->parameters) {
            $this->storedQuery->setParameters($this->parameters);
            $defaults = array();
            foreach ($this->parameters as $key => $value) {
                $defaults[$key] = array('value' => $value);
            }
            $defaults = array(self::CONT_PARAMS => $defaults);
            $this['parametrizeForm']->setDefaults($defaults);
        }
        if (!$this->isAuthorized()) {
            $this->template->error = _('Nedostatečné oprávnění.');
        } else {
            $this->template->error = $this->getSqlError();
        }
        $this->template->hasParameters = $this->showParametrize && count($this->storedQuery->getQueryPattern()->getParameters());

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'StoredQueryComponent.latte');
        $this->template->render();
    }

    public function handleCSV($header = true) {
        if ($this->parameters) {
            $this->storedQuery->setParameters($this->parameters);
        }
        if (!$this->isAuthorized()) {
            throw new ForbiddenRequestException();
        }

        $data = array();
        foreach ($this->storedQuery->getData() as $row) {
            $data[] = $row;
        }

        $name = isset($this->storedQuery->getQueryPattern()->name) ? $this->storedQuery->getQueryPattern()->name : 'adhoc';
        $name .= '.csv';
        //TODO move to configuration
        $response = new CSVResponse($data, $name);
        $response->setAddHeading($header);
        $response->setQuotes(false);
        $response->setGlue(';');

        $this->presenter->sendResponse($response);
    }

    private function isAuthorized() {
        $implicitParameters = $this->storedQuery->getImplicitParameters();
        if (!isset($implicitParameters[StoredQueryFactorySQL::PARAM_CONTEST])) {
            return false;
        }
        return $this->contestAuthorizator->isAllowed($this->storedQuery, 'execute', $implicitParameters[StoredQueryFactorySQL::PARAM_CONTEST]);
    }

}
