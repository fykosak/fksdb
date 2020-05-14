<?php

namespace FKSDB\Components\Controls;

use Authorization\ContestAuthorizator;
use Exports\ExportFormatFactory;
use Exports\StoredQuery;
use Exports\StoredQueryFactory as StoredQueryFactorySQL;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\StoredQueryFactory;
use FKSDB\Components\Grids\StoredQueryGrid;
use FKSDB\Exceptions\NotFoundException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use PDOException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryComponent extends Control {

    const CONT_PARAMS = 'params';
    const PARAMETER_URL_PREFIX = 'p_';

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
     *
     * @var ExportFormatFactory
     */
    private $exportFormatFactory;

    /**
     * @var null|bool|string
     */
    private $error;

    /**
     * @var bool
     */
    private $showParametrize = true;
    /**
     * @var Container
     */
    private $container;

    /**
     * StoredQueryComponent constructor.
     * @param StoredQuery $storedQuery
     * @param ContestAuthorizator $contestAuthorizator
     * @param StoredQueryFactory $storedQueryFormFactory
     * @param ExportFormatFactory $exportFormatFactory
     * @param Container $container
     */
    function __construct(StoredQuery $storedQuery, ContestAuthorizator $contestAuthorizator, StoredQueryFactory $storedQueryFormFactory, ExportFormatFactory $exportFormatFactory, Container $container) {
        parent::__construct();
        $this->storedQuery = $storedQuery;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->storedQueryFormFactory = $storedQueryFormFactory;
        $this->exportFormatFactory = $exportFormatFactory;
        $this->container = $container;
    }

    /**
     * @return bool
     */
    public function getShowParametrize() {
        return $this->showParametrize;
    }

    /**
     * @param $showParametrize
     */
    public function setShowParametrize($showParametrize) {
        $this->showParametrize = $showParametrize;
    }

    /**
     * @param $parameters
     */
    public function setParameters($parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @param $parameters
     */
    public function updateParameters($parameters) {
        if (!$this->parameters) {
            $this->parameters = [];
        }
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * @return StoredQueryGrid
     */
    protected function createComponentGrid() {
        return new StoredQueryGrid($this->storedQuery, $this->container);
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentParametrizeForm() {
        $control = new FormControl();
        $form = $control->getForm();

        $queryPattern = $this->storedQuery->getQueryPattern();
        $parameters = $this->storedQueryFormFactory->createParametersValues($queryPattern);
        $form->addComponent($parameters, self::CONT_PARAMS);

        $form->addSubmit('execute', _('Parametrizovat'));
        $form->onSuccess[] = function (Form $form) {
            $this->parameters = [];
            $values = $form->getValues();
            foreach ($values[self::CONT_PARAMS] as $key => $values) {
                $this->parameters[$key] = $values['value'];
            }
        };
        return $control;
    }

    /**
     * @return bool|null|string
     */
    public function getSqlError() {
        if ($this->error === null) {
            $this->error = false;
            try {
                $this->storedQuery->getColumnNames(); // this may throw PDOException in the main query
            } catch (PDOException $exception) {
                $this->error = $exception->getMessage();
            }
        }
        return $this->error;
    }

    public function render() {
        if ($this->parameters) {
            $this->storedQuery->setParameters($this->parameters);
            $defaults = [];
            foreach ($this->parameters as $key => $value) {
                $defaults[$key] = ['value' => $value];
            }
            $defaults = [self::CONT_PARAMS => $defaults];
            $this->getComponent('parametrizeForm')->getForm()->setDefaults($defaults);
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

    /**
     * @param $format
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function handleFormat($format) {
        if ($this->parameters) {
            $this->storedQuery->setParameters($this->parameters);
        }
        if (!$this->isAuthorized()) {
            throw new ForbiddenRequestException();
        }
        try {
            $exportFormat = $this->exportFormatFactory->createFormat($format, $this->storedQuery);
            $response = $exportFormat->getResponse();
            $this->presenter->sendResponse($response);
        } catch (InvalidArgumentException $exception) {

            throw new NotFoundException(sprintf('Neznámý formát \'%s\'.', $format), $exception);
        }
    }

    /**
     * @return bool
     */
    private function isAuthorized() {
        $implicitParameters = $this->storedQuery->getImplicitParameters();
        /*
         * Beware, that when export doesn't depend on contest_id directly further checks has to be done!
         */
        if (!isset($implicitParameters[StoredQueryFactorySQL::PARAM_CONTEST])) {
            return false;
        }
        return $this->contestAuthorizator->isAllowed($this->storedQuery, 'execute', $implicitParameters[StoredQueryFactorySQL::PARAM_CONTEST]);
    }

}
