<?php

namespace FKSDB\Components\Controls\StoredQuery;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Exports\ExportFormatFactory;
use FKSDB\Models\StoredQuery\StoredQuery;
use FKSDB\Models\StoredQuery\StoredQueryFactory as StoredQueryFactorySQL;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Factories\StoredQueryFactory;
use FKSDB\Components\Grids\StoredQuery\ResultsGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotFoundException;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use PDOException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ResultsComponent extends BaseComponent {

    public const CONT_PARAMS = 'params';
    public const PARAMETER_URL_PREFIX = 'p_';
    /**
     * @persistent
     */
    public ?array $parameters = [];
    private ?StoredQuery $storedQuery = null;
    private ContestAuthorizator $contestAuthorizator;
    private StoredQueryFactory $storedQueryFormFactory;
    private ExportFormatFactory $exportFormatFactory;
    /** @var null|bool|string */
    private $error;
    private bool $showParametrizeForm = true;

    final public function injectPrimary(ContestAuthorizator $contestAuthorizator, StoredQueryFactory $storedQueryFormFactory, ExportFormatFactory $exportFormatFactory): void {
        $this->contestAuthorizator = $contestAuthorizator;
        $this->storedQueryFormFactory = $storedQueryFormFactory;
        $this->exportFormatFactory = $exportFormatFactory;
    }

    public function setShowParametrizeForm(bool $showParametersForm): void {
        $this->showParametrizeForm = $showParametersForm;
    }

    public function setStoredQuery(StoredQuery $storedQuery): void {
        $this->storedQuery = $storedQuery;
    }

    private function hasStoredQuery(): bool {
        return isset($this->storedQuery);
    }

    public function setParameters(array $parameters): void {
        $this->parameters = $parameters;
    }

    public function updateParameters(array $parameters): void {
        if (!$this->parameters) {
            $this->parameters = [];
        }
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    protected function createComponentGrid(): ResultsGrid {
        return new ResultsGrid($this->storedQuery, $this->getContext());
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentParametrizeForm(): FormControl {
        $control = new FormControl($this->getContext());
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
     * @throws \ReflectionException
     */
    public function render(): void {
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
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.results.latte');
        $this->template->render();
    }

    /**
     * @param string $format
     * @return void
     * @throws AbortException
     * @throws ForbiddenRequestException
     * @throws NotFoundException
     */
    public function handleFormat(string $format): void {
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
            throw new NotFoundException(sprintf('Undefined format \'%s\'.', $format), $exception);
        }
    }

    /**
     * TODO is this really need?
     * G*/
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
