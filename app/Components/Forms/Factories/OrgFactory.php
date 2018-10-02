<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\ORM\ModelContest;
use Nette\Forms\ControlGroup;
use Nette\Forms\Form;
use ServicePerson;
use YearCalculator;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class OrgFactory {

    /**
     * @var ServicePerson
     */
    private $servicePerson;

    /**
     * @var YearCalculator
     */
    private $yearCalculator;

    function __construct(ServicePerson $servicePerson,YearCalculator $yearCalculator) {
        $this->servicePerson = $servicePerson;
        $this->yearCalculator = $yearCalculator;
    }

    public function createOrg($options = 0,ControlGroup $group = null,ModelContest $contest) {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);


        $min = $this->yearCalculator->getFirstYear($contest);
        $max = $this->yearCalculator->getLastYear($contest);

        $container->addText('since',_('Od ročníku'))
                ->addRule(Form::NUMERIC)
                ->addRule(Form::FILLED)
                ->addRule(Form::RANGE,_('Počáteční ročník není v intervalu [%d, %d].'), [$min,$max]);

        $container->addText('until',_('Do ročníku'))
                ->addCondition(Form::FILLED)
                ->addRule(Form::NUMERIC)
                ->addRule(function ($until,$since) {
                    return $since->value <= $until->value;
                },_('Konec nesmí být dříve než začátek'),$container['since'])
                ->addRule(Form::RANGE,_('Koncový ročník není v intervalu [%d, %d].'), [$min,$max]);


        $container->addText('role',_('Funkce'))
                ->addRule(Form::MAX_LENGTH,null,255);


        $container->addText('tex_signature',_('TeX identifikátor'))
                ->addRule(Form::MAX_LENGTH,null,32)
                ->addCondition(Form::FILLED)
                ->addRule(Form::REGEXP,_('%label obsahuje nepovolené znaky.'),'/^[a-z][a-z0-9._\-]*$/i');


        $container->addText('domain_alias',_('Jméno v doméně fykos.cz'))
                ->addRule(Form::MAX_LENGTH,null,32)
                ->addCondition(Form::FILLED)
                ->addRule(Form::REGEXP,_('%l obsahuje nepovolené znaky.'),'/^[a-z][a-z0-9._\-]*$/i');

        $container->addSelect('order',_('Hodnost'))
                ->setOption('description',_('Pro řazení v seznamu organizátorů'))
                ->setItems([
                    0 => '0 - org',
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4 - hlavní organizátor',
                    9 => '9 - vedoucí semináře',
                ])
                ->setPrompt(_('Zvolit hodnost'))
                ->addRule(Form::FILLED,_('Vyberte hodnost.'));

        $container->addTextArea('contribution',_('Co udělal'))
                ->setOption('description',_('Zobrazeno v síni slávy'));

        return $container;
    }

}
