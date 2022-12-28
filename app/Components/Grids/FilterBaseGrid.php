<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\Form;
use Nette\InvalidStateException;

abstract class FilterBaseGrid extends BaseGrid
{
    /** @persistent */
    public ?array $searchTerm = null;

    public function render(): void
    {
        // this has to be done already here (and in the parent call again :-( )
        if (isset($this->searchTerm)) {
            $this->dataSource->applyFilter($this->searchTerm);
        }
        parent::render();
    }

    /**
     * @throws NotImplementedException
     */
    protected function getData(): SearchableDataSource
    {
        return parent::getData();
    }

    public function isSearchable(): bool
    {
        return true;
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentSearchForm(): FormControl
    {
        if (!$this->isSearchable()) {
            throw new InvalidStateException('Cannot create search form without searchable data source.');
        }
        $control = new FormControl($this->container);
        $form = $control->getForm();
        $form->setMethod(\Nette\Forms\Form::GET);
        $form->addText('term')
            ->setDefaultValue($this->searchTerm['term'])
            ->setHtmlAttribute('placeholder', _('Find'));
        $form->addSubmit('submit', _('Search'));
        $form->onSuccess[] = function (Form $form): void {
            $this->searchTerm = $form->getValues('array');
        };
        return $control;
    }
}
