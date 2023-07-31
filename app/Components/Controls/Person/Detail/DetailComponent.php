<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Components\Grids\Components\BaseList;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\UI\Title;
use Nette\DI\Container;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseList<M>
 */
abstract class DetailComponent extends BaseList
{
    protected PersonModel $person;
    protected bool $isOrg;

    public function __construct(
        Container $container,
        PersonModel $person,
        int $userPermissions,
        bool $isOrg
    ) {
        parent::__construct($container, $userPermissions);
        $this->isOrg = $isOrg;
        $this->person = $person;
    }

    final public function render(): void
    {
        $this->template->headline = $this->getHeadline();
        parent::render();
    }

    protected function getTemplatePath(): string
    {
        if ($this->userPermission < $this->getMinimalPermissions()) {
            return __DIR__ . DIRECTORY_SEPARATOR . 'permissionDenied.latte';
        }
        return __DIR__ . DIRECTORY_SEPARATOR . 'list.latte';
    }

    abstract protected function getHeadline(): Title;

    abstract protected function getMinimalPermissions(): int;
}
