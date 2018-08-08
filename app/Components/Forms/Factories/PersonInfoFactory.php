<?php

namespace FKSDB\Components\Forms\Factories;

use FKS\Components\Forms\Controls\URLTextBox;
use FKS\Components\Forms\Controls\WriteonlyDatePicker;
use FKS\Components\Forms\Controls\WriteonlyInput;
use FKSDB\Components\Forms\Rules\BornNumber;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;
use Nette\InvalidArgumentException;
use Nette\Utils\Html;
use Nette\DateTime;
use \ReactField;

/**
 * Class PersonHistoryFactory
 * @package FKSDB\Components\Forms\Factories
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonInfoFactory {

    private function createBorn() {
        return (new WriteonlyDatePicker(_('Datum narození')))
            ->setDefaultDate((new DateTime())->modify('-16 years'));
    }

    private function createIdNumber() {
        return (new WriteonlyInput(_('Číslo OP')))
            ->setOption('description', _('U cizinců číslo pasu.'))
            ->addRule(Form::MAX_LENGTH, null, 32);
    }

    private function createBornId() {
        $control = new WriteonlyInput(_('Rodné číslo'));
        $control->setOption('description', _('U cizinců prázdné.'))
            ->addCondition(Form::FILLED)
            ->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));
        return $control;
    }


    private function createPhoneParentM() {
        return $this->rawPhone(_('Telefonní číslo (matka)'));
    }

    private function createPhoneParentD() {
        return $this->rawPhone(_('Telefonní číslo (otec)'));
    }

    private function createPhone() {
        return $this->rawPhone(_('Telefonní číslo'));
    }

    private function createIm() {
        return (new WriteonlyInput(_('ICQ, Jabber, apod.')))
            ->addRule(Form::MAX_LENGTH, null, 32);
    }

    private function createBirthplace() {
        return (new WriteonlyInput(_('Místo narození')))
            ->setOption('description', _('Město a okres (kvůli diplomům).'))
            ->addRule(Form::MAX_LENGTH, null, 255);
    }

    private function createUkLogin() {
        return (new WriteonlyInput(_('Login UK')))
            ->addRule(Form::MAX_LENGTH, null, 8);
    }

    private function createAccount() {
        return (new WriteonlyInput(_('Číslo bankovního účtu')))
            ->addRule(Form::MAX_LENGTH, null, 32);
    }


    private function createCareer() {
        return (new TextArea(_('Co právě dělá')))
            ->setOption('description', _('Zobrazeno v seznamu organizátorů'));
    }

    private function createHomepage() {
        return (new URLTextBox(_('Homepage')));
    }

    private function createNote() {
        return (new TextArea(_('Poznámka')));
    }

    private function createOrigin() {
        return (new TextArea(_('Jak jsi se o nás dozvěděl(a)?')));
    }

    public function createAgreed() {
        $link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->addAttributes(['href' => _("http://fykos.cz/doc/souhlas.pdf")]);
        return (new Checkbox(_('Souhlasím se zpracováním osobních údajů')))
            ->setOption('description', $link);
    }

    private function createEmail() {
        $control = new TextInput(_('E-mail'));
        $control->addCondition(Form::FILLED)
            ->addRule(Form::EMAIL, _('Neplatný tvar e-mailu.'));
        return $control;
    }

    private function rawPhone($label) {
        $control = new WriteonlyInput($label);
        $control->setAttribute("placeholder", 've tvaru +420123456789');
        $control->addRule(Form::MAX_LENGTH, null, 32)
            ->addCondition(Form::FILLED)
            ->addRule(Form::REGEXP, _('%label smí obsahovat jen číslice a musí být v mezinárodím tvaru začínajícím +421 nebo +420.'), '/\+42[01]\d{9}/');
        return $control;
    }

    /**
     * @param string $fieldName
     * @return \FKS\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox|\Nette\Forms\Controls\BaseControl
     */
    public function createField($fieldName) {
        switch ($fieldName) {
            case 'born':
                return $this->createBorn();
            case   'id_number':
                return $this->createIdNumber();
            case    'born_id':
                return $this->createBornId();
            case    'phone_parent_m':
                return $this->createPhoneParentM();
            case    'phone_parent_d':
                return $this->createPhoneParentD();
            case    'phone':
                return $this->createPhone();
            case    'im':
                return $this->createIm();
            case    'birthplace':
                return $this->createBirthplace();
            case   'uk_login':
                return $this->createUkLogin();
            case    'account':
                return $this->createAccount();
            case    'career':
                return $this->createCareer();
            case    'homepage':
                return $this->createHomepage();
            case    'note':
                return $this->createNote();
            case    'origin':
                return $this->createOrigin();
            case    'aggred':
                return $this->createAgreed();
            case    'email':
                return $this->createEmail();
            default:
                throw new InvalidArgumentException();
        }

    }

    private function createReactBorn() {
        return new ReactField(true, _('Datum narození'));
    }

    private function createReactIdNumber() {
        return new ReactField(true, _('Číslo OP'), _('U cizinců číslo pasu.'));
        //->addRule(Form::MAX_LENGTH, null, 32);
    }

    private function createReactBornId() {
        return new ReactField(true, _('Rodné číslo'), _('U cizinců prázdné.'));

        //->addRule(new BornNumber(), _('Rodné číslo nemá platný formát.'));

    }


    private function createReactPhoneParentM() {
        throw new \Nette\NotImplementedException();
        // return $this->rawPhone(_('Telefonní číslo (matka)'));
    }

    private function createReactPhoneParentD() {
        throw new \Nette\NotImplementedException();
        // return $this->rawPhone(_('Telefonní číslo (otec)'));
    }

    private function createReactPhone() {
        throw new \Nette\NotImplementedException();
        // return $this->rawPhone(_('Telefonní číslo'));
    }

    private function createReactIm() {
        throw new \Nette\NotImplementedException();
        /*return (new WriteonlyInput(_('ICQ, Jabber, apod.')))
            ->addRule(Form::MAX_LENGTH, null, 32);*/
    }

    private function createReactBirthplace() {
        throw new \Nette\NotImplementedException();
        /*return (new WriteonlyInput(_('Místo narození')))
            ->setOption('description', _('Město a okres (kvůli diplomům).'))
            ->addRule(Form::MAX_LENGTH, null, 255);*/
    }

    private function createReactUkLogin() {
        throw new \Nette\NotImplementedException();
        /*return (new WriteonlyInput(_('Login UK')))
            ->addRule(Form::MAX_LENGTH, null, 8);*/
    }

    private function createReactAccount() {
        throw new \Nette\NotImplementedException();
        /*return (new WriteonlyInput(_('Číslo bankovního účtu')))
            ->addRule(Form::MAX_LENGTH, null, 32);*/
    }


    private function createReactCareer() {
        throw new \Nette\NotImplementedException();
        /*return (new TextArea(_('Co právě dělá')))
            ->setOption('description', _('Zobrazeno v seznamu organizátorů'));*/
    }

    private function createReactHomepage() {
        throw new \Nette\NotImplementedException();
        // return (new URLTextBox(_('Homepage')));
    }

    private function createReactNote() {
        return new ReactField(false, _('Poznámka'));
    }

    private function createReactOrigin() {
        return new ReactField(false, _('Jak jsi se o nás dozvěděl(a)?'));
    }

    public function createReactAgreed() {
        throw new \Nette\NotImplementedException();
        /*$link = Html::el('a');
        $link->setText(_('Text souhlasu'));
        $link->addAttributes(['href' => _("http://fykos.cz/doc/souhlas.pdf")]);
        return (new Checkbox(_('Souhlasím se zpracováním osobních údajů')))
            ->setOption('description', $link);*/
    }

    private function createReactEmail() {
        return new ReactField(false, _('E-mail'));
    }

    /**
     * @param string $fieldName
     * @return ReactField
     */
    public function createReactField($fieldName) {
        switch ($fieldName) {
            case 'born':
                return $this->createReactBorn();
            case   'id_number':
                return $this->createReactIdNumber();
            case    'born_id':
                return $this->createReactBornId();
            //case    'phone_parent_m':
            //    return $this->createReactPhoneParentM();
            //case    'phone_parent_d':
            //    return $this->createReactPhoneParentD();
            //case    'phone':
            //    return $this->createReactPhone();
            //case    'im':
            //    return $this->createReactIm();
            //case    'birthplace':
            //    return $this->createReactBirthplace();
            //case   'uk_login':
            //    return $this->createReactUkLogin();
            //case    'account':
            //    return $this->createReactAccount();
            //case    'career':
            //    return $this->createReactCareer();
            //case    'homepage':
            //    return $this->createReactHomepage();
            case    'note':
                return $this->createReactNote();
            case    'origin':
                return $this->createReactOrigin();
            //case    'aggred':
            //    return $this->createReactAgreed();
            case    'email':
                return $this->createReactEmail();
            default:
                throw new InvalidArgumentException();
        }

    }
}
