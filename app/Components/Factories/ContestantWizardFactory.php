<?php

namespace FKSDB\Components\Factories;

use FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\PersonProvider;
use FKSDB\Components\Forms\Factories\AddressFactory;
use FKSDB\Components\Forms\Factories\ContestantFactory;
use FKSDB\Components\Forms\Factories\PersonFactory;
use FKSDB\Components\WizardComponent;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Application\UI\Form;
use Nette\Utils\Html;
use ServicePerson;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ContestantWizardFactory {

    const STEP_PERSON = 'person';
    const STEP_DATA = 'data';
    const SEND = 'send';

    /* Important elements */
    const EL_PERSON_ID = 'person_id';
    const CONT_PERSON = 'person';
    const CONT_CONTESTANT = 'contestant';
    const CONT_PERSON_INFO = 'person_info';
    const CONT_ADDRESSES = 'addresses';

    /* Important groups */
    const GRP_PERSON = 'personGrp';

    /**
     * @var PersonFactory
     */
    private $personFactory;

    /**
     * @var ContestantFactory
     */
    private $contestantFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var ServicePerson
     */
    private $personService;

    /**
     *
     * @var PersonProvider
     */
    private $personProvider;

    function __construct(PersonFactory $personFactory, ContestantFactory $contestantFactory, AddressFactory $addressFactory, ServicePerson $personService, PersonProvider $personProvider) {
        $this->personFactory = $personFactory;
        $this->contestantFactory = $contestantFactory;
        $this->addressFactory = $addressFactory;
        $this->personService = $personService;
        $this->personProvider = $personProvider;
    }

    /**
     * 
     * @return WizardComponent
     */
    public function create() {
        $wizard = new WizardComponent();

        $wizard->setFirstStep(self::STEP_PERSON);

        $personForm = $this->createPersonForm();
        $wizard->addStep($personForm, self::STEP_PERSON, self::STEP_DATA);
        $wizard->registerStepSubmitter(self::STEP_PERSON, self::SEND);


        $dataForm = $this->createDataForm();
        $wizard->addStep($dataForm, self::STEP_DATA);
        $wizard->registerStepSubmitter(self::STEP_DATA, self::SEND);

        return $wizard;
    }

    private function createPersonForm() {
        $form = new Form();

        $group = $form->addGroup('Existující osoba');

        $renderMethod = 'return $("<li>")
                        .append("<a>" + item.label + "<br>" + item.place + ", ID: " + item.value + "</a>")
                        .appendTo(ul);';
        $personElement = new AutocompleteSelectBox(true, 'Jméno', $renderMethod);
        $personElement->setDataProvider($this->personProvider);
        

        // TODO validate non-existent contestant or restrict selection
        $personElement->addCondition(Form::FILLED)->toggle(self::GRP_PERSON, false);
        $form->addComponent($personElement, self::EL_PERSON_ID);

        $group = $form->addGroup('Nová osoba');
        $group->setOption('container', Html::el('fieldset')->id(self::GRP_PERSON));
        $personContainer = $this->personFactory->createPerson(PersonFactory::SHOW_DISPLAY_NAME | PersonFactory::SHOW_GENDER, $group, array(
            PersonFactory::IDX_CONTROL => $personElement,
            PersonFactory::IDX_OPERATION => ~Form::FILLED,
            PersonFactory::IDX_VALUE => null,
        ));
        $form->addComponent($personContainer, self::CONT_PERSON);

        $form->setCurrentGroup();

        $form->addSubmit(self::SEND, 'Pokračovat');
        return $form;
    }

    private function createDataForm() {
        $form = new Form();

        /*
         * Person
         */
        $group = $form->addGroup('Osoba');
        $personContainer = $this->personFactory->createPerson(PersonFactory::DISABLED, $group);
        $form->addComponent($personContainer, self::CONT_PERSON);

        /*
         * Contestant
         */
        $group = $form->addGroup('Řešitel');
        $contestantContainer = $this->contestantFactory->createContestant(null, $group);
        $form->addComponent($contestantContainer, self::CONT_CONTESTANT);


        /**
         * Addresses
         */
        $group = $form->addGroup('Adresa');
        $factory = $this->addressFactory;
        $replicator = new Replicator(function($replContainer) use($factory, $group) {
                    $factory->buildAddress($replContainer, $group);
                    $replContainer->addComponent($factory->createTypeElement(), 'type');

                    $replContainer->addSubmit('remove', 'Odebrat')->addRemoveOnClick();
                }, 1, true);
        $replicator->containerClass = 'FKSDB\Components\Forms\Containers\ModelContainer';

        $form->addComponent($replicator, self::CONT_ADDRESSES);

        $replicator->addSubmit('add', 'Přidat adresu')->addCreateOnClick();


        /**
         * Personal information
         */
        $group = $form->addGroup('Osobní informace');
        $infoContainer = $this->personFactory->createPersonInfo(PersonFactory::SHOW_EMAIL | PersonFactory::SHOW_LOGIN_CREATION, $group);
        $form->addComponent($infoContainer, self::CONT_PERSON_INFO);

        $form->setCurrentGroup();

        $form->addSubmit(self::SEND, 'Dokončit');
        return $form;
    }

}
