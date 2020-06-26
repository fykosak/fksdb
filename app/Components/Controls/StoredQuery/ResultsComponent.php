<?php

namespace FKSDB\Components\Controls;

use Authorization\ContestAuthorizator;
use Exports\ExportFormatFactory;
use FKSDB\StoredQuery\StoredQuery;
use FKSDB\StoredQuery\StoredQueryFactory as StoredQueryFactorySQL;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\StoredQueryFactory;
use FKSDB\Components\Grids\StoredQueryGrid;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\NotFoundException;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use PDOException;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ResultsComponent extends BaseComponent {

    const CONT_PARAMS = 'params';
    const PARAMETER_URL_PREFIX = 'p_';

    /**
     * @persistent
     * @var array
     */
    public $parameters = [];

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
    private $showParametrizeForm = true;

    /**
     * @param ContestAuthorizator $contestAuthorizator
     * @param StoredQueryFactory $storedQueryFormFactory
     * @param ExportFormatFactory $exportFormatFactory
     * @return void
     */
    public function injectPrimary(ContestAuthorizator $contestAuthorizator, StoredQueryFactory $storedQueryFormFactory, ExportFormatFactory $exportFormatFactory) {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->storedQueryFormFactory = $storedQueryFormFactory;
        $this->exportFormatFactory = $exportFormatFactory;
    }

    /**
     * @param bool $showParametersForm
     * @return void
     */
    public function setShowParametrizeForm(bool $showParametersForm) {
        $this->showParametrizeForm = $showParametersForm;
    }

    /**
     * @param StoredQuery $query
     * @return void
     */
    public function setStoredQuery(StoredQuery $query) {
        $this->storedQuery = $query;
    }

    private function hasStoredQuery(): bool {
        return isset($this->storedQuery) && !is_null($this->storedQuery);
    }

    /**
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters) {
        $this->parameters = $parameters;
    }

    /**
     * @param $parameters
     * @return void
     */
    public function updateParameters(array $parameters) {
        if (!$this->parameters) {
            $this->parameters = [];
        }
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    protected function createComponentGrid(): StoredQueryGrid {
        return new StoredQueryGrid($this->storedQuery, $this->getContext());
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentParametrizeForm(): FormControl {
        $control = new FormControl();
        $form = $control->getForm();

        $parameters = $this->storedQueryFormFactory->createParametersValues($this->storedQuery->getQueryPattern()->getParameters());
        $form->addComponent($parameters, self::CONT_PARAMS);

        $form->addSubmit('execute', _('Execute'));
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

    /**
     * @return void
     * @throws BadTypeException
     */
    public function render() {
        if ($this->parameters) {
            $this->storedQuery->setParameters($this->parameters);
            $defaults = [];
            foreach ($this->parameters as $key => $value) {
                $defaults[$key] = ['value' => $value];
            }
            /** @var FormControl $formControl */
            $formControl = $this->getComponent('parametrizeForm');
            $formControl->getForm()->setDefaults([self::CONT_PARAMS => $defaults]);
        }
        $this->template->error = $this->isAuthorized() ? $this->getSqlError() : _('Permission denied');
        $this->template->hasParameters = $this->showParametrizeForm && count($this->storedQuery->getQueryParameters());
        $this->template->hasStoredQuery = $this->hasStoredQuery();
        $this->template->storedQuery = $this->storedQuery ?? null;
        $this->template->formats = $this->storedQuery ? $this->exportFormatFactory->getFormats($this->storedQuery) : [];
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'results.latte');
        $this->template->render();
    }

    /**
     * @param string $format
     * @return void
     * @throws AbortException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     */
    public function handleFormat(string $format) {
        if ($this->parameters) {
            $this->storedQuery->setParameters($this->parameters);
        }
        if (!$this->isAuthorized()) {
            throw new ForbiddenRequestException();
        }
        try {
            $response = $this->exportFormatFactory->createFormat($format, $this->storedQuery)->getResponse();
            $this->presenter->sendResponse($response);
        } catch (InvalidArgumentException $exception) {
            throw new NotFoundException(sprintf('Neznámý formát \'%s\'.', $format), $exception);
        }
    }

    // TODO is this really need?
    private function isAuthorized(): bool {
        if (!$this->hasStoredQuery()) {
            return false;
        }
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
