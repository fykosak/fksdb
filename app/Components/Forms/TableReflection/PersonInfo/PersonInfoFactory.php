<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Factories\PersonInfo\AccountField;
use FKSDB\Components\Forms\Factories\PersonInfo\AgreedField;
use FKSDB\Components\Forms\Factories\PersonInfo\BirthplaceField;
use FKSDB\Components\Forms\Factories\PersonInfo\BornField;
use FKSDB\Components\Forms\Factories\PersonInfo\BornIdField;
use FKSDB\Components\Forms\Factories\PersonInfo\CareerField;
use FKSDB\Components\Forms\Factories\PersonInfo\EmailField;
use FKSDB\Components\Forms\Factories\PersonInfo\HomepageField;
use FKSDB\Components\Forms\Factories\PersonInfo\IdNumberField;
use FKSDB\Components\Forms\Factories\PersonInfo\ImField;
use FKSDB\Components\Forms\Factories\PersonInfo\NoteField;
use FKSDB\Components\Forms\Factories\PersonInfo\OriginField;
use FKSDB\Components\Forms\Factories\PersonInfo\PhoneField;
use FKSDB\Components\Forms\Factories\PersonInfo\PhoneParentDField;
use FKSDB\Components\Forms\Factories\PersonInfo\PhoneParentMField;
use FKSDB\Components\Forms\Factories\PersonInfo\UkLoginField;
use FKSDB\Components\Forms\TableReflection\TableReflectionFactory;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;

/**
 * Class PersonHistoryFactory
 * @package FKSDB\Components\Forms\Factories
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonInfoFactory extends TableReflectionFactory {

    public function createField(string $fieldName, array $data = []): BaseControl {
        switch ($fieldName) {
            case   'born':
                return new BornField();
            case   'id_number':
                return new IdNumberField();
            case    'born_id':
                return new BornIdField();
            case    'phone_parent_m':
                return new PhoneParentMField();
            case    'phone_parent_d':
                return new PhoneParentDField();
            case    'phone':
                return new PhoneField();
            case    'im':
                return new ImField();
            case    'birthplace':
                return new BirthplaceField();
            case   'uk_login':
                return new UkLoginField();
            case    'account':
                return new AccountField();
            case    'career':
                return new CareerField();
            case    'homepage':
                return new HomepageField();
            case    'note':
                return new NoteField();
            case    'origin':
                return new OriginField();
            case    'agreed':
                return new AgreedField();
            case    'email':
                return new EmailField();
            default:
                throw new InvalidArgumentException();
        }

    }
}
