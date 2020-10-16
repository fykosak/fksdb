<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Components\Forms\FormComponent;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Exceptions\ModelException;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\ServicePersonInfo;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;

/**
 * Class PreferredLangForm
 * @package FKSDB\Components\Forms\Controls
 */
class PreferredLangFormComponent extends FormComponent {

    protected ModelPerson $person;

    protected ServicePersonInfo $servicePersonInfo;

    public function __construct(Container $container, ModelPerson $person) {
        parent::__construct($container);
        $this->person = $person;
    }

    final public function injectServicePersonInfo(ServicePersonInfo $servicePersonInfo): void {
        $this->servicePersonInfo = $servicePersonInfo;
    }

    protected function appendSubmitButton(Form $form): SubmitButton {
        return $form->addSubmit('submit', _('Save'));
    }

    protected function handleSuccess(SubmitButton $button): void {
        $form = $button->getForm();
        $values = $form->getValues();
        $lang = $values['preferred_lang'];
        try {
            $this->servicePersonInfo->store($this->person, $this->person->getInfo(), ['preferred_lang' => $lang]);
            $this->flashMessage(_('Preferred language has been set'), Message::LVL_SUCCESS);
            $this->getPresenter()->redirect('this');
        } catch (ModelException $exception) {
            $this->flashMessage(_('Error'), Message::LVL_DANGER);
        }
    }

    protected function configureForm(Form $form): void {
        $items = [];
        foreach ($this->getTranslator()->getSupportedLanguages() as $lang) {
            $items[$lang] = LanguageChooser::$languageNames[$lang];
        }
        $form->addRadioList('preferred_lang')->setItems($items);
    }

    /**
     * @return void
     * @throws BadTypeException
     */
    public function render(): void {
        $this->getForm()->setDefaults(['preferred_lang' => $this->person->getPreferredLang()]);
        parent::render();
    }
}
