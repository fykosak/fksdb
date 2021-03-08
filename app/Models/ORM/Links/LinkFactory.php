<?php

namespace FKSDB\Models\ORM\Links;

use FKSDB\Models\ORM\ReferencedAccessor;
use FKSDB\Models\Entity\CannotAccessModelException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

/**
 * Class AbstractLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class LinkFactory {

    protected string $modelClassName;

    public function __construct(?string $modelClassName = null) {
        if ($modelClassName) {
            $this->modelClassName = $modelClassName;
        }
    }

    /**
     * @param Presenter $presenter
     * @param AbstractModelSingle $model
     * @return string
     * @throws InvalidLinkException
     * @throws CannotAccessModelException
     */
    public function create(Presenter $presenter, AbstractModelSingle $model): string {
        return $presenter->link(...$this->createLinkParameters($model));
    }

    /**
     * @param AbstractModelSingle $modelSingle
     * @return AbstractModelSingle|null
     * @throws CannotAccessModelException
     */
    protected function getModel(AbstractModelSingle $modelSingle): ?AbstractModelSingle {
        if (!isset($this->modelClassName)) {
            return $modelSingle;
        }
        return ReferencedAccessor::accessModel($modelSingle, $this->modelClassName);
    }

    /**
     * @param AbstractModelSingle $model
     * @return array
     * @throws CannotAccessModelException
     * @throws InvalidLinkException
     */
    public function createLinkParameters(AbstractModelSingle $model): array {
        $model = $this->getModel($model);
        if (is_null($model)) {
            throw new InvalidLinkException();
        }
        return [
            $this->getDestination($model),
            $this->prepareParams($model),
        ];
    }

    abstract protected function getDestination(AbstractModelSingle $model): string;

    abstract protected function prepareParams(AbstractModelSingle $model): array;
}
