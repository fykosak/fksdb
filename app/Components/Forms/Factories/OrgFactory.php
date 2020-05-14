<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\YearCalculator;
use Nette\Forms\Controls\BaseControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class OrgFactory extends SingleReflectionFactory {

    /**
     * @var \FKSDB\ORM\Services\ServicePerson
     */
    private $servicePerson;

    /**
     * @var \FKSDB\YearCalculator
     */
    private $yearCalculator;

    /**
     * OrgFactory constructor.
     * @param ServicePerson $servicePerson
     * @param \FKSDB\YearCalculator $yearCalculator
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ServicePerson $servicePerson, YearCalculator $yearCalculator, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->servicePerson = $servicePerson;
        $this->yearCalculator = $yearCalculator;
    }

    /**
     * @param ModelContest $contest
     * @return ModelContainer
     * @throws \Exception
     */
    public function createOrg(ModelContest $contest): ModelContainer {
        $container = new ModelContainer();

        foreach (['since', 'until', 'role', 'tex_signature', 'domain_alias', 'order', 'contribution'] as $field) {
            $control = $this->createField($field, $contest);
            $container->addComponent($control, $field);
        }
        return $container;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_ORG;
    }

    /**
     * @param string $fieldName
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function createField(string $fieldName, ...$args): BaseControl {

        switch ($fieldName) {
            case 'since':
            case 'until':
                list($contest) = $args;
                $min = $this->yearCalculator->getFirstYear($contest);
                $max = $this->yearCalculator->getLastYear($contest);
                return $this->loadFactory($fieldName)->createField($min, $max);
            default:
                return parent::createField($fieldName);

        }
    }

}
