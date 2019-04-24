<?php

namespace FKSDB\Components\Grids\Validation;

use FKSDB\Components\Controls\Helpers\ValuePrinters\PersonValueControl;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\ValidationTest\ValidationLog;
use FKSDB\ValidationTest\ValidationTest;
use Nette\NotImplementedException;
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
            $person = ModelPerson::createFromActiveRow($row);
            return PersonValueControl::getGridValue($this,$person);
        });
        foreach ($this->tests as $test) {
            $this->addColumn($test::getAction(), $test::getTitle())->setRenderer(function ($row) use ($test) {
                $person = ModelPerson::createFromActiveRow($row);
                $log = $test->run($person);
                return self::createHtmlLog($log);
            });
        }
    }

    /**
     * @param ValidationLog $log
     * @return Html
     * @throws NotImplementedException
     */
    protected static function createHtmlLog(ValidationLog $log): Html {
        $icon = Html::el('span');
        switch ($log->getLevel()) {
            case ValidationLog::LVL_DANGER:
                $icon->addAttributes(['class' => 'fa fa-close']);
                break;
            case ValidationLog::LVL_WARNING:
                $icon->addAttributes(['class' => 'fa fa-warning']);
                break;
            case ValidationLog::LVL_INFO:
                $icon->addAttributes(['class' => 'fa fa-info']);
                break;
            case ValidationLog::LVL_SUCCESS:
                $icon->addAttributes(['class' => 'fa fa-check']);
                break;
            default:
                throw new NotImplementedException(\sprintf('%s is not supported', $log->getLevel()));
        }
        return Html::el('span')->addAttributes([
            'class' => 'text-' . $log->getLevel(),
            'title' => $log->getMessage(),
        ])->addHtml($icon);
    }
}
