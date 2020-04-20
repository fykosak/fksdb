<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePerson;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class ParticipantAcquaintanceChartControl
 * @package FKSDB\Components\Controls\Chart
 */
class TotalPersonsChartControl extends ReactComponent implements IChart {
    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * TotalPersonsChartControl constructor.
     * @param Container $context
     */
    public function __construct(Container $context) {
        parent::__construct($context);
        $this->servicePerson = $context->getByType(ServicePerson::class);
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return 'totalPersons';
    }

    /**
     * @return string
     * @throws JsonException
     */
    function getData(): string {
        $query = $this->servicePerson->getTable()->order('created');
        $data = [];
        /** @var ModelPerson $person */
        foreach ($query as $person) {
            $data[] = [
                'created' => $person->created->format('c'),
                'gender' => $person->gender,
                'personId' => $person->person_id,
            ];
        }
        return Json::encode($data);
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Total persons in FKSDB');
    }

    /**
     * @return Control
     */
    public function getControl(): Control {
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getReactId(): string {
        return 'chart.total-person';
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return _('Graf zobrazuje vývoj počtu osôb vo FKSDB a priradené person_id v daný čas.');
    }
}
