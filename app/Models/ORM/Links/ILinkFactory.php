<?php

namespace FKSDB\Models\ORM\Links;

use FKSDB\Models\ORM\Models\AbstractModelSingle;
use Nette\Application\UI\Presenter;

interface ILinkFactory {

    public function create(Presenter $presenter, AbstractModelSingle $model): string;

    public function createLinkParameters(AbstractModelSingle $model): array;

    public function getText(): string;
}
