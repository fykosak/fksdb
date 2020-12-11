<?php

namespace FKSDB\Components\Grids;

use FKSDB\Model\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Model\ORM\Models\ModelPerson;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;
use NiftyGrid\DuplicateGlobalButtonException;

/**
 * Class StalkingGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonRelatedGrid extends BaseGrid {

    protected ModelPerson $person;

    protected array $definition;

    protected int $userPermissions;

    public function __construct(string $section, ModelPerson $person, int $userPermissions, Container $container) {
        $this->definition = $container->getParameters()['components'][$section];
        parent::__construct($container);
        $this->person = $person;
        $this->userPermissions = $userPermissions;
    }

    protected function getData(): IDataSource {
        $query = $this->person->related($this->definition['table']);
        if ($this->definition['minimalPermission'] > $this->userPermissions) {
            $query->where('1=0');
            $this->flashMessage('Access denied', Message::LVL_ERROR);
        }
        return new NDataSource($query);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws DuplicateGlobalButtonException
     * @throws InvalidLinkException
     */
    protected function configure(IPresenter $presenter): void {
        $this->paginate = false;
        parent::configure($presenter);
        $this->addColumns($this->definition['rows'], $this->userPermissions);
        foreach ($this->definition['links'] as $link) {
            $this->addLink($link);
        }
        $this->addCSVDownloadButton();
    }

    protected function getModelClassName(): string {
        return $this->definition['model'];
    }
}
