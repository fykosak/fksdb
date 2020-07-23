<?php

namespace FKSDB\DBReflection\LinkFactories;

use FKSDB\DBReflection\ReferencedFactory;
use FKSDB\Entity\CannotAccessModelException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;

/**
 * Class AbstractLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractLink implements ILinkFactory {

    /** @var ReferencedFactory */
    protected $referencedFactory;

    /**
     * @param ReferencedFactory $factory
     * @return void
     */
    public function setReferencedFactory(ReferencedFactory $factory) {
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
    protected function getModel(AbstractModelSingle $modelSingle) {
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
