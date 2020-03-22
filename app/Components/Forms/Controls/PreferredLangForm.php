<?php

namespace FKSDB\Components\Forms\Controls;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\LangPresenter;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

/**
 * Class PreferredLangForm
 * @package FKSDB\Components\Forms\Controls
 */
class PreferredLangForm extends FormControl {
    /** @var Container */
    private $servicePerson;

    /**
     * PreferredLangForm constructor.
     * @param Container $container
     * @param ITranslator $translator
     * @throws BadRequestException
     */
    public function __construct(Container $container, ITranslator $translator) {
        parent::__construct();
        $this->container = $container;
        $form = $this->getForm();
        $items = [];
        foreach ($translator->getSupportedLanguages() as $lang) {
            $items[$lang] = LangPresenter::LANGUAGE_NAMES[$lang];
        }
        $form->addRadioList('preferred_lang')->setItems($items);
        $form->addSubmit('submit', _('Save'));
        $form->onSuccess[] = function (Form $form) {
            $this->handleChangeLang($form);
        };
    }

    /**
     * @param Form $form
     */
    private function handleChangeLang(Form $form) {

    }
}
