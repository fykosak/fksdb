<?php

namespace FKSDB\Components\Grids\Application\Person;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPerson;
use Nette\Application\UI\Presenter;
use Nette\DI\Container;

/**
 * Class MyApplicationsGrid
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class PersonApplicationsGrid extends BaseGrid {

    protected ModelPerson $person;

    /**
     * MyApplicationsGrid constructor.
     * @param ModelPerson $person
     * @param Container $container
     */
    public function __construct(ModelPerson $person, Container $container) {
        parent::__construct($container);
        $this->person = $person;
    }

    protected function configure(Presenter $presenter): void {
        parent::configure($presenter);
        $this->paginate = false;
    }
}
