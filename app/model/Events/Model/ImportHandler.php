<?php

namespace FKSDB\Events\Model;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Events\Model\Grid\SingleEventSource;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Utils\CSVParser;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\SmartObject;
use Nette\Utils\JsonException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ImportHandler {

    use SmartObject;

    const STATELESS_IGNORE = 'ignore';
    const STATELESS_KEEP = 'keep';

    /**
     * @var Container
     */
    private $container;

    /**
     * @var SingleEventSource
     */
    private $source;

    /**
     * @var CSVParser
     */
    private $parser;

    /**
     *
     * @var string
     */
    private $keyName;

    /**
     * ImportHandler constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param CSVParser $parser
     * @param string $keyName
     * @return void
     */
    public function setInput(CSVParser $parser, string $keyName) {
        $this->parser = $parser;
        $this->keyName = $keyName;
    }

    /**
     * @param SingleEventSource $source
     * @return void
     */
    public function setSource(SingleEventSource $source) {
        $this->source = $source;
    }

    /**
     * @param ApplicationHandler $handler
     * @param string $transitions
     * @param string $errorMode
     * @param string $stateless
     * @return bool
     * @throws NeonSchemaException
     * @throws BadRequestException
     * @throws JsonException
     */
    public function import(ApplicationHandler $handler, string $transitions, string $errorMode, string $stateless): bool {
        set_time_limit(0);
        $holdersMap = $this->createHoldersMap();
        $primaryBaseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $baseHolderName = $primaryBaseHolder->getName();

        $handler->setErrorMode($errorMode);
        $handler->beginTransaction();
        $hasError = false;
        foreach ($this->parser as $row) {
            $values = $this->rowToValues($row);
            $keyValue = $values[$baseHolderName][$this->keyName];
            if (!isset($values[$baseHolderName][BaseHolder::STATE_COLUMN]) || !$values[$baseHolderName][BaseHolder::STATE_COLUMN]) {
                if ($stateless == self::STATELESS_IGNORE) {
                    continue;
                } elseif ($stateless == self::STATELESS_KEEP) {
                    unset($values[$baseHolderName][BaseHolder::STATE_COLUMN]);
                }
            }
            /** @var EventDispatchFactory $factory */
            $factory = $this->container->getByType(EventDispatchFactory::class);
            $holder = isset($holdersMap[$keyValue]) ? $holdersMap[$keyValue] : $factory->getDummyHolder($this->source->getEvent());
            try {
                switch ($transitions) {
                    case ApplicationHandler::STATE_OVERWRITE:
                        $handler->store($holder, $values);
                        break;
                    case ApplicationHandler::STATE_TRANSITION:
                        $handler->storeAndExecute($holder, $values);
                        break;
                    default:
                        throw new InvalidArgumentException();
                }
            } catch (ApplicationHandlerException $exception) {
                $hasError = true;
                if ($errorMode == ApplicationHandler::ERROR_ROLLBACK) {
                    throw new ImportHandlerException(_('Import se nepovedl.'), null, $exception);
                }
            }
        }
        $handler->commit(true);
        return !$hasError;
    }

    private function prepareColumnName(string $columnName, BaseHolder $baseHolder): array {
        $parts = explode('.', $columnName);
        if (count($parts) == 1) {
            return [$baseHolder->getName(), $parts[0]];
        } else {
            return $parts;
        }
    }

    /**
     * @param mixed $row
     * @return array
     */
    private function rowToValues($row): array {
        $primaryBaseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $values = [];
        $fieldExists = false;
        $fieldNames = array_keys($primaryBaseHolder->getFields());
        foreach ($row as $columnName => $value) {
            if (is_numeric($columnName)) { // hack for new PDO
                continue;
            }
            list($baseHolderName, $fieldName) = $this->prepareColumnName($columnName, $primaryBaseHolder);

            if (!isset($values[$baseHolderName])) {
                $values[$baseHolderName] = [];
            }
            $values[$baseHolderName][$fieldName] = $value;
            if (in_array($fieldName, $fieldNames)) {
                $fieldExists = true;
            }
        }
        if (!$fieldExists) {
            throw new ImportHandlerException(_('CSV soubor neobsahuje platnou hlavičku.'));
        }
        return $values;
    }

    /**
     * @return Holder[]
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    private function createHoldersMap(): array {
        $primaryBaseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $pkName = $primaryBaseHolder->getService()->getTable()->getPrimary();

        $result = [];
        foreach ($this->source->getHolders() as $pkValue => $holder) {
            if ($this->keyName == $pkName) {
                $keyValue = $pkValue;
            } else {
                $fields = $holder->getPrimaryHolder()->getFields();
                $keyValue = $fields[$this->keyName]->getValue();
            }
            $result[$keyValue] = $holder;
        }
        return $result;
    }
}
