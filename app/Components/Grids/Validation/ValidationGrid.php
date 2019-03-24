<?php

namespace FKSDB\Components\Grids\Validation;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;
use Nette\Utils\Html;
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
            return Html::el('a')->addAttributes([
                'href' => $this->getPresenter()->link(':Org:Stalking:view', ['id' => $person->person_id]),
            ])->add($person->getFullName());
        });
        foreach ($this->tests as $test) {
            $this->addColumn($test::getAction(), $test::getTitle())->setRenderer(function ($row) use ($test) {
                $person = ModelPerson::createFromTableRow($row);
                $log = $test->run($person);
                return self::createHtmlLog($log);
            });
        }
    }

    /**
     * @param ValidationLog $log
     * @return Html
     */
    protected static function createHtmlLog(ValidationLog $log): Html {
        return Html::el('span')->addAttributes(['class' => 'mr-3 badge badge-' . $log->level])->add($log->message);

    }
}
