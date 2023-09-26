<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Components\Referenced;

use FKSDB\Models\Exceptions\BadTypeException;
use Nette\DI\Container;

/**
 * @phpstan-template TModel of \Fykosak\NetteORM\Model
 * @phpstan-template TModelHelper of \Fykosak\NetteORM\Model
 * @phpstan-extends TemplateItem<TModel,TModelHelper>
 */
class SimpleItem extends TemplateItem
{
    /**
     * @throws BadTypeException
     * @throws \ReflectionException
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
