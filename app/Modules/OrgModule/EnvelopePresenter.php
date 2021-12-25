<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\PDFGenerators\Envelopes\ContestToPerson\PageComponent;
use FKSDB\Components\PDFGenerators\Providers\AbstractPageComponent;
use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\ServicePerson;
use Fykosak\Utils\UI\PageTitle;
use Nette\Database\Explorer;
use Nette\Forms\Form;

class EnvelopePresenter extends BasePresenter
{

    private Explorer $readOnlyExplorer;
    private ServicePerson $servicePerson;
    /** @persistent */
    public array $personIds = [];
    /** @persistent */
    public string $format = AbstractPageComponent::FORMAT_B5_LANDSCAPE;

    public function injectDatabase(ServicePerson $servicePerson): void
    {
        $this->readOnlyExplorer = $this->getContext()->getService('database.ro.context');
        $this->servicePerson = $servicePerson;
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Envelopes for person generator'), 'fa fa-envelope');
    }

    protected function createComponentOutput(): ProviderComponent
    {
        return new ProviderComponent(
            new PageComponent($this->getSelectedContest(), $this->getContext(), $this->format),
            $this->servicePerson->getTable()->where('person_id', $this->personIds),
            $this->getContext(),
        );
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addTextArea('sql', _('SQL query'));
        $form->addText('attribute', _('Person_id attribute'))->setDefaultValue('person_id');
        $form->addSelect(
            'format',
            _('Format'),
            PageComponent::getAvailableFormats(),
        );

        $testButton = $form->addSubmit('preview', _('Preview'));
        $testButton->onClick[] = fn(Form $form) => $this->handleTest($form);

        $printButton = $form->addSubmit('print', _('Print'));
        $printButton->onClick[] = fn(Form $form) => $this->handlePrint($form);
        return $control;
    }

    private function handleTest(Form $form): void
    {
        $this->personIds = $this->getPersonIdsFromSQL($form);
        $this->format = $form->getValues()['format'];
    }

    private function getPersonIdsFromSQL(Form $form): array
    {
        $values = $form->getValues('array');
        $personIds = [];
        foreach ($this->readOnlyExplorer->query($values['sql']) as $row) {
            $personIds[] = $row->{$values['attribute']};
        }
        return $personIds;
    }

    private function handlePrint(Form $form): void
    {
        $this->redirect(
            'output',
            [
                'personIds' => $this->getPersonIdsFromSQL($form),
                'format' => $form->getValues()['format'],
            ],
        );
    }
}
