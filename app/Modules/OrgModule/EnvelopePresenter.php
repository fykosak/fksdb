<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\PDFGenerators\Envelopes\ContestToPerson\PageComponent;
use FKSDB\Components\PDFGenerators\Providers\AbstractProviderComponent;
use FKSDB\Components\PDFGenerators\Providers\DefaultProviderComponent;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Services\ServicePerson;
use Fykosak\NetteORM\TypedTableSelection;
use Nette\Database\Explorer;
use Nette\Forms\Form;

class EnvelopePresenter extends BasePresenter
{

    private TypedTableSelection $persons;

    private Explorer $readOnlyExplorer;
    private ServicePerson $servicePerson;

    public function injectDatabase(ServicePerson $servicePerson): void
    {
        $this->readOnlyExplorer = $this->getContext()->getService('database.ro.context');
        $this->servicePerson = $servicePerson;
    }

    public function actionOutput(): void
    {
        $personIds = $this->getParameter('persons');
        $this->persons = $this->servicePerson->getTable()->where('person_id', $personIds);
    }

    protected function createComponentOutput(): DefaultProviderComponent
    {
        return new DefaultProviderComponent(
            new PageComponent($this->getSelectedContest(), $this->getContext()),
            AbstractProviderComponent::FORMAT_B5_LANDSCAPE,
            $this->persons ?? [],
            $this->getContext()
        );
    }

    /**
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentSelectForm(): FormControl
    {
        $control = new FormControl($this->getContext());
        $form = $control->getForm();
        $form->addTextArea('sql', _('SQL'))->setOption(
            'description',
            _(
                'SQL dotaz, 
            ktorý obahuje slpec rovnaký ako klúčový attribut v ktorom sa nachádza person_id osoby, 
            pre vybratie jednej osoby použite "select 324 as person_id from dual"'
            )
        );
        $form->addText('attribute', _('Person_id attribute'))->setDefaultValue('person_id');
        $testButton = $form->addSubmit('test', _('Test'));
        $testButton->onClick[] = fn(Form $form) => $this->handleTest($form);

        $printButton = $form->addSubmit('print', _('Print'));
        $printButton->onClick[] = fn(Form $form) => $this->handlePrint($form);
        return $control;
    }

    private function handleTest(Form $form): void
    {
        $this->persons = $this->servicePerson->getTable()->where('person_id', $this->getPersonIdsFromSQL($form));
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
        $this->redirect('output', ['persons' => $this->getPersonIdsFromSQL($form)]);
    }
}
