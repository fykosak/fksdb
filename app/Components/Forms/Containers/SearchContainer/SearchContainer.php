<?php

namespace FKSDB\Components\Forms\Containers\SearchContainer;

use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Controls\ReferencedId;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;

/**
 * Class SearchContainer
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class SearchContainer extends ContainerWithOptions {

    protected const CSS_AJAX = 'ajax';

    protected const CONTROL_SEARCH = '_c_search';
    protected const SUBMIT_SEARCH = '__search';

    protected ReferencedId $referencedId;

    public function setReferencedId(ReferencedId $referencedId): void {
        $this->referencedId = $referencedId;
        $control = $this->createSearchControl();
        if ($control) {
            $this->addComponent($control, self::CONTROL_SEARCH);
            $this->createSearchButton();
        }
    }

    public function isSearchSubmitted(): bool {
        return $this->getForm(false)
            && $this->getComponent(self::SUBMIT_SEARCH, false)
            && $this->getComponent(self::SUBMIT_SEARCH)->isSubmittedBy();
    }

    protected function createSearchButton(): void {
        $submit = $this->addSubmit(self::SUBMIT_SEARCH, _('Find'));
        $submit->setValidationScope(false);

        $submit->getControlPrototype()->class[] = self::CSS_AJAX;

        $submit->onClick[] = function (SubmitButton $button) {
            $term = $this->getComponent(self::CONTROL_SEARCH)->getValue();
            $model = ($this->getSearchCallback())($term);

            $values = [];
            if (!$model) {
                $model = ReferencedId::VALUE_PROMISE;
                $values = ($this->getTermToValuesCallback())($term);
            }
            $this->referencedId->setValue($model);
            $this->referencedId->getReferencedContainer()->setValues($values);
            $this->referencedId->invalidateFormGroup();
        };
    }

    abstract protected function createSearchControl(): ?BaseControl;

    abstract protected function getSearchCallback(): callable;

    abstract protected function getTermToValuesCallback(): callable;
}
