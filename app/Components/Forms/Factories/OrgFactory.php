<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelContest;
use FKSDB\ORM\Services\ServicePerson;
use FKSDB\YearCalculator;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Tracy\Debugger;

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
    function __construct(ServicePerson $servicePerson, YearCalculator $yearCalculator, TableReflectionFactory $tableReflectionFactory) {
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

        $min = $this->yearCalculator->getFirstYear($contest);
        $max = $this->yearCalculator->getLastYear($contest);
        foreach (['since'] as $field) {
            $control = $this->createField($field, $contest);
            $container->addComponent($control, 'since');
        }

        $container->addText('until', _('Do ročníku'))
            ->addCondition(Form::FILLED)
            ->addRule(Form::NUMERIC)
            /* ->addRule(function ($until, $since) {
                 return $since->value <= $until->value;
             }, _('Konec nesmí být dříve než začátek'), $container['since'])*/
            ->addRule(Form::RANGE, _('Koncový ročník není v intervalu [%d, %d].'), [$min, $max]);

        $roleControl = $this->tableReflectionFactory->createFieldCallback(DbNames::TAB_ORG, 'role')();
        $container->addComponent($roleControl, 'role');

        $container->addText('tex_signature', _('TeX identifikátor'))
            ->addRule(Form::MAX_LENGTH, null, 32)
            ->addCondition(Form::FILLED)
            ->addRule(Form::REGEXP, _('%label obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');


        $container->addText('domain_alias', _('Jméno v doméně fykos.cz'))
            ->addRule(Form::MAX_LENGTH, null, 32)
            ->addCondition(Form::FILLED)
            ->addRule(Form::REGEXP, _('%l obsahuje nepovolené znaky.'), '/^[a-z][a-z0-9._\-]*$/i');

        $container->addSelect('order', _('Hodnost'))
            ->setOption('description', _('Pro řazení v seznamu organizátorů'))
            ->setItems([
                0 => '0 - org',
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4 - hlavní organizátor',
                9 => '9 - vedoucí semináře',
            ])
            ->setPrompt(_('Zvolit hodnost'))
            ->addRule(Form::FILLED, _('Vyberte hodnost.'));

        $container->onValidate[] = function ($container) {
            Debugger::barDump($container);
            die();
        };
        $container->addTextArea('contribution', _('Co udělal'))
            ->setOption('description', _('Zobrazeno v síni slávy'));

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
     * @param ModelContest $contest
     * @return mixed
     * @throws \Exception
     */
    public function createField(string $fieldName, ModelContest $contest = null): BaseControl {
        switch ($fieldName) {
            case 'since':
                $min = $this->yearCalculator->getFirstYear($contest);
                $max = $this->yearCalculator->getLastYear($contest);
                return $this->loadFactory($fieldName)->createField($min, $max);
                break;
            default:
                return parent::createField($fieldName);

        }
    }

}
