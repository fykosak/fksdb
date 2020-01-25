<?php

namespace Events\Model;

use Events\Model\Grid\SingleEventSource;
use Events\Model\Holder\BaseHolder;
use FKSDB\Utils\CSVParser;
use Nette\DI\Container;
use Nette\SmartObject;
use Nette\Utils\ArrayHash;

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
    function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param CSVParser $parser
     * @param $keyName
     */
    public function setInput(CSVParser $parser, $keyName) {
        $this->parser = $parser;
        $this->keyName = $keyName;
    }

    /**
     * @param SingleEventSource $source
     */
    public function setSource(SingleEventSource $source) {
        $this->source = $source;
    }

    /**
     * @param ApplicationHandler $handler
     * @param $transitions
     * @param $errorMode
     * @param $stateless
     * @return bool
     * @throws \Nette\Utils\JsonException
     */
    public function import(ApplicationHandler $handler, $transitions, $errorMode, $stateless) {
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
                } else if ($stateless == self::STATELESS_KEEP) {
                    unset($values[$baseHolderName][BaseHolder::STATE_COLUMN]);
                }
            }

            $holder = isset($holdersMap[$keyValue]) ? $holdersMap[$keyValue] : $this->container->createEventHolder($this->source->getEvent());
            try {
                if ($transitions == ApplicationHandler::STATE_OVERWRITE) {
                    $handler->store($holder, $values);
                } elseif ($transitions == ApplicationHandler::STATE_TRANSITION) {
                    $handler->storeAndExecute($holder, $values);
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

    /**
     * @param $row
     * @return ArrayHash
     */
    private function rowToValues($row) {
        $primaryBaseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $values = new ArrayHash();
        $fieldExists = false;
        $fieldNames = array_keys($primaryBaseHolder->getFields());
        foreach ($row as $columnName => $value) {
            $parts = explode('.', $columnName);
            if (count($parts) == 1) {
                $baseHolderName = $primaryBaseHolder->getName();
                $fieldName = $parts[0];
            } else {
                list($baseHolderName, $fieldName) = $parts;
            }
            if (!isset($values[$baseHolderName])) {
                $values[$baseHolderName] = new ArrayHash();
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
     * @return array
     */
    private function createHoldersMap() {
        $primaryBaseHolder = $this->source->getDummyHolder()->getPrimaryHolder();
        $pkName = $primaryBaseHolder->getService()->getTable()->getPrimary();

        $result = [];
        foreach ($this->source as $pkValue => $holder) {
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
