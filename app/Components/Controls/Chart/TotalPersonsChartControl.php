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
 * Class TotalPersonsChartControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TotalPersonsChartControl extends ReactComponent implements IChart {
    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * TotalPersonsChartControl constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        parent::__construct($container, 'chart.total-person');
    }

    /**
     * @param ServicePerson $servicePerson
     * @return void
     */
    public function injectServicePerson(ServicePerson $servicePerson) {
        $this->servicePerson = $servicePerson;
    }
    /**
     * @param mixed ...$args
     * @return string
     * @throws JsonException
     */
    public function getData(...$args): string {
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

    public function getTitle(): string {
        return _('Total persons in FKSDB');
    }

    public function getControl(): Control {
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription() {
        return _('Graf zobrazuje vývoj počtu osôb vo FKSDB a priradené person_id v daný čas.');
    }
}
