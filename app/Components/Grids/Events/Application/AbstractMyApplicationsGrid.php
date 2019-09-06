<?php

namespace FKSDB\Components\Grids\Events\Application;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class AbstractMyApplicationsGrid
 * @package FKSDB\Components\Grids\Events\Application
 */
abstract class AbstractMyApplicationsGrid extends BaseGrid {
    /**
     * @var ModelPerson
     */
    protected $person;

    /**
     * AbstractMyApplicationsGrid constructor.
     * @param ModelPerson $person
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ModelPerson $person, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->person = $person;
    }
}
