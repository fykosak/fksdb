<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Referenced;

use Fykosak\NetteORM\Model\Model;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of Model
 * @phpstan-template TModelHelper of Model
 * @phpstan-extends TemplateItem<TModel,TModelHelper>
 */
class SimpleItem extends TemplateItem
{
    /**
     * @phpstan-param (callable(TModel):TModelHelper)|null $modelAccessorHelper
     */
    public function __construct(
        Container $container,
        string $templateString,
        ?callable $modelAccessorHelper = null
    ) {
        parent::__construct($container, $templateString . ':value', $templateString . ':title', $modelAccessorHelper);
    }
}
