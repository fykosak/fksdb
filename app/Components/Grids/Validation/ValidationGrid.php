<?php

namespace FKSDB\Components\Grids\Validation;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\ValidationTest;
use NiftyGrid\DataSource\NDataSource;

/**
 * Class ValidationGrid
 * @package FKSDB\Components\Grids\Validation
 */
class ValidationGrid extends BaseGrid {
    /**
     * @var ServicePerson
     */
    private $servicePerson;
    /**
     * @var ValidationTest[]
     */
    private $tests;

    /**
     * ValidationGrid constructor.
     * @param ServicePerson $servicePerson
     * @param ValidationTest[] $tests
     */
    public function __construct(ServicePerson $servicePerson, array $tests) {
        parent::__construct();
        $this->servicePerson = $servicePerson;
        $this->tests = $tests;
    }

    /**
     * @param \AuthenticatedPresenter $presenter
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $persons = $this->servicePerson->getTable();
        $dataSource = new NDataSource($persons);
        $this->setDataSource($dataSource);

        $this->addColumn('display_name', _('Person'))->setRenderer(function ($row) {
            $person = ModelPerson::createFromTableRow($row);
            return $person->getFullName();
        });
        foreach ($this->tests as $test) {
            $test::configureGrid($this);
        }
    }

    /**
     * @param string $name
     * @param null $label
     * @param null $width
     * @param null $truncate
     * @return \NiftyGrid\Components\Column
     * @throws \NiftyGrid\DuplicateColumnException
     */
    public function addColumn($name, $label = NULL, $width = NULL, $truncate = NULL) {
        return parent::addColumn($name, $label, $width, $truncate);
    }
}
