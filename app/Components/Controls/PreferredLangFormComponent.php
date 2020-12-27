<?php

namespace FKSDB\Components\Controls;

use FKSDB\Components\Controls\Choosers\LanguageChooser;
use FKSDB\Components\Controls\FormComponent\FormComponent;

use FKSDB\Models\Exceptions\BadTypeException;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Services\ServicePersonInfo;
use Fykosak\Utils\ORM\Exceptions\ModelException;

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
            $this->flashMessage(_('Error'), Message::LVL_ERROR);
        }
    }

    protected function configureForm(Form $form): void {
        $items = [];
        foreach ($this->translator->getSupportedLanguages() as $lang) {
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
