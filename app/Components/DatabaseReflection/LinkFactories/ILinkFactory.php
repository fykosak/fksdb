<?php

namespace FKSDB\Components\DatabaseReflection\LinkFactories;

use FKSDB\ORM\AbstractModelSingle;
use Nette\Application\UI\Component;
use Nette\Application\UI\Presenter;
use Nette\Utils\Html;

interface ILinkFactory {

    public function create(Presenter $presenter, AbstractModelSingle $model): string;

    public function createLinkParameters(AbstractModelSingle $model): array;

    public function getText(): string;
}
