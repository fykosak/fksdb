<?php

namespace FKSDB\Components\Controls\Chart;

use FKSDB\Components\React\ReactComponent;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServiceEvent;
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
     * @param ServicePerson $servicePerson
     */
    public function __construct(Container $context, ServicePerson $servicePerson) {
        parent::__construct($context);
        $this->servicePerson = $servicePerson;
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return 'totalPersons';
    }

    /**
     * @return string
     */
    function getMode(): string {
        return '';
    }

    /**
     * @return string
     * @throws JsonException
     */
    function getData(): string {
        $query = $this->servicePerson->getTable()->order('created');
        $data = [];
        foreach ($query as $row) {

            $person = ModelPerson::createFromActiveRow($row);
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
    function getComponentName(): string {
        return 'total-persons';
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return _('Total persons in FKSDB');
    }

    /**
     * @return string
     */
    function getModuleName(): string {
        return 'chart';
    }

    /**
     * @return Control
     */
    public function getControl(): Control {
        return $this;
    }
}
