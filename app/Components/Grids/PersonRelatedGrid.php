<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;

/**
 * @template M of \Fykosak\NetteORM\Model
 * @phpstan-extends BaseGrid<M>
 */
class PersonRelatedGrid extends BaseGrid
{
    protected PersonModel $person;
    /** @phpstan-var array{'table':string,'minimalPermission':int,'rows':string[],'links':string[]} */
    protected array $definition;
    protected int $userPermissions;

    public function __construct(string $section, PersonModel $person, int $userPermissions, Container $container)
    {
        $this->definition = $container->getParameters()['components'][$section];
        parent::__construct($container);
        $this->person = $person;
        $this->userPermissions = $userPermissions;
    }

    /**
     * @phpstan-return TypedGroupedSelection<M>
     */
    protected function getModels(): TypedGroupedSelection
    {
        /** @phpstan-var TypedGroupedSelection<M> $query */
        $query = $this->person->related($this->definition['table']);
        if ($this->definition['minimalPermission'] > $this->userPermissions) {
            $query->where('1=0');
            $this->flashMessage('Access denied', Message::LVL_ERROR);
        }
        return $query;
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->paginate = false;
        $this->addColumns($this->definition['rows']);
        foreach ($this->definition['links'] as $link) {
            $this->addORMLink($link);
        }
        // $this->addCSVDownloadButton();
    }
}
