<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use Fykosak\NetteORM\TypedGroupedSelection;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\PersonModel;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

class PersonRelatedGrid extends BaseGrid
{

    protected PersonModel $person;
    protected array $definition;
    protected FieldLevelPermissionValue $userPermissions;

    public function __construct(
        string $section,
        PersonModel $person,
        FieldLevelPermissionValue $userPermissions,
        Container $container
    ) {
        $this->definition = $container->getParameters()['components'][$section];
        parent::__construct($container);
        $this->person = $person;
        $this->userPermissions = $userPermissions;
    }

    /**
     * @return IDataSource
     * @throws BadTypeException
     */
    protected function getData(): IDataSource
    {
        $query = $this->person->related($this->definition['table']);
        if (!$query instanceof TypedGroupedSelection) {
            throw new BadTypeException(TypedGroupedSelection::class, $query);
        }
        if ($this->definition['minimalPermission'] > $this->userPermissions->value) {
            $query->where('1=0');
            $this->flashMessage('Access denied', Message::LVL_ERROR);
        }
        return new NDataSource($query);
    }

    /**
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(Presenter $presenter): void
    {
        $this->paginate = false;
        parent::configure($presenter);
        $this->addColumns($this->definition['rows'], $this->userPermissions);
        foreach ($this->definition['links'] as $link) {
            $this->addLink($link);
        }
        $this->addCSVDownloadButton();
    }
}
