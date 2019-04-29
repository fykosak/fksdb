<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Factories\PersonInfo\AcademicDegreePrefixField;
use FKSDB\Components\Forms\Factories\PersonInfo\AcademicDegreeSuffixField;
use FKSDB\Components\Forms\Factories\PersonInfo\AccountField;
use FKSDB\Components\Forms\Factories\PersonInfo\AgreedField;
use FKSDB\Components\Forms\Factories\PersonInfo\BirthplaceField;
use FKSDB\Components\Forms\Factories\PersonInfo\BornField;
use FKSDB\Components\Forms\Factories\PersonInfo\BornIdField;
use FKSDB\Components\Forms\Factories\PersonInfo\CareerField;
use FKSDB\Components\Forms\Factories\PersonInfo\CitizenshipField;
use FKSDB\Components\Forms\Factories\PersonInfo\EmailField;
use FKSDB\Components\Forms\Factories\PersonInfo\EmployerField;
use FKSDB\Components\Forms\Factories\PersonInfo\HomepageField;
use FKSDB\Components\Forms\Factories\PersonInfo\IdNumberField;
use FKSDB\Components\Forms\Factories\PersonInfo\ImField;
use FKSDB\Components\Forms\Factories\PersonInfo\LinkedinIdField;
use FKSDB\Components\Forms\Factories\PersonInfo\NoteField;
use FKSDB\Components\Forms\Factories\PersonInfo\OriginField;
use FKSDB\Components\Forms\Factories\PersonInfo\PhoneField;
use FKSDB\Components\Forms\Factories\PersonInfo\PhoneParentDField;
use FKSDB\Components\Forms\Factories\PersonInfo\PhoneParentMField;
use FKSDB\Components\Forms\Factories\PersonInfo\UkLoginField;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Services\ServiceRegion;
use Nette\Forms\Controls\BaseControl;
use Nette\InvalidArgumentException;

/**
 * Class PersonHistoryFactory
 * @package FKSDB\Components\Forms\Factories
 * @author Michal Červeňák <miso@fykos.cz>
 */
class PersonInfoFactory extends SingleReflectionFactory {
    /**
     * @var ServiceRegion
     */
    private $serviceRegion;

    /**
     * PersonInfoFactory constructor.
     * @param ServiceRegion $serviceRegion
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ServiceRegion $serviceRegion, TableReflectionFactory $tableReflectionFactory) {
        parent::__construct($tableReflectionFactory);
        $this->serviceRegion = $serviceRegion;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_PERSON_INFO;
    }

    /**
     * @param $fieldName
     * @return BaseControl
     * @throws \Exception
     */
    public function createField(string $fieldName): BaseControl {
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
            case 'academic_degree_prefix':
                return new AcademicDegreePrefixField();
            case 'academic_degree_suffix':
                return new AcademicDegreeSuffixField();
            case'employer':
                return new EmployerField();
            case 'health_insurance':
                return parent::createField($fieldName);
            case 'citizenship':
                return new CitizenshipField($this->serviceRegion);
            case 'linkedin_id':
                return new LinkedinIdField();
            default:
                throw new InvalidArgumentException('Field ' . $fieldName . ' not exists');
        }

    }
}
