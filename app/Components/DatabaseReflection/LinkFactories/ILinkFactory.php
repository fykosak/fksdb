<?php

namespace FKSDB\Components\DatabaseReflection\LinkFactories;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\UI\Component;
use Nette\Utils\Html;

interface ILinkFactory {
    /**
     * @param Component $component
     * @return void
     */
    public function setComponent(Component $component);

    public function __invoke(AbstractModelSingle $model): Html;

    public function getText(): string;

    public function getDestination(AbstractModelSingle $model): string;

    public function prepareParams(AbstractModelSingle $model): array;
}
