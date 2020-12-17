<?php

namespace FKSDB\Model\DBReflection\LinkFactories;

use FKSDB\Model\DBReflection\ReferencedFactory;
use FKSDB\Model\Entity\CannotAccessModelException;
use FKSDB\Model\Exceptions\BadTypeException;
use FKSDB\Model\ORM\Models\AbstractModelSingle;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

/**
 * Class AbstractLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractLink implements ILinkFactory {

    protected ReferencedFactory $referencedFactory;

    public function setReferencedFactory(ReferencedFactory $factory): void {
        $this->referencedFactory = $factory;
    }

    /**
     * @param Presenter $presenter
     * @param AbstractModelSingle $model
     * @return string
     * @throws BadTypeException
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
     * @throws BadTypeException
     */
    protected function getModel(AbstractModelSingle $modelSingle): ?AbstractModelSingle {
        return $this->referencedFactory->accessModel($modelSingle);
    }

    /**
     * @param AbstractModelSingle $model
     * @return array
     * @throws CannotAccessModelException
     * @throws BadTypeException
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
