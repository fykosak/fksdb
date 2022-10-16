<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Containers\SearchContainer;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Controls\ReferencedId;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

abstract class SearchContainer extends ContainerWithOptions
{
    protected const CONTROL_SEARCH = '_c_search';
    protected const SUBMIT_SEARCH = '__search';

    protected ReferencedId $referencedId;

    public function setReferencedId(ReferencedId $referencedId): void
    {
        $this->referencedId = $referencedId;
        $control = $this->createSearchControl();
        if ($control) {
            $this->addComponent($control, self::CONTROL_SEARCH);
            $this->createSearchButton();
        }
    }

    public function isSearchSubmitted(): bool
    {
        return $this->getForm(false)
            && $this->getComponent(self::SUBMIT_SEARCH, false)
            && $this->getComponent(self::SUBMIT_SEARCH)->isSubmittedBy();
    }

    protected function createSearchButton(): void
    {
        $submit = $this->addSubmit(
            self::SUBMIT_SEARCH,
            Html::el('span')->addHtml(
                Html::el('i')->addAttributes(['class' => 'fa fa-search me-3'])
            )->addText(_('Find'))
        );
        $submit->setValidationScope([$this->getComponent(self::CONTROL_SEARCH)]);

        $cb = function (): void {
            $term = $this->getComponent(self::CONTROL_SEARCH)->getValue();
            $model = ($this->getSearchCallback())($term);

            $values = [];
            if (!$model) {
                $model = ReferencedId::VALUE_PROMISE;
                $values = ($this->getTermToValuesCallback())($term);
            }
            $this->referencedId->setValue($model);
            $this->referencedId->referencedContainer->setValues($values);
        };
        $submit->onClick[] = $cb;
        $submit->onInvalidClick[] = $cb;
    }

    abstract protected function createSearchControl(): ?BaseControl;

    abstract protected function getSearchCallback(): callable;

    abstract protected function getTermToValuesCallback(): callable;
}
