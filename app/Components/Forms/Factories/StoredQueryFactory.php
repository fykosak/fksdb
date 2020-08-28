<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\StoredQueryTagTypeProvider;
use FKSDB\Components\Forms\Controls\SQLConsole;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;
use FKSDB\ORM\Services\StoredQuery\ServiceStoredQueryTagType;
use Kdyby\Extension\Forms\Replicator\Replicator;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Forms\ControlGroup;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class StoredQueryFactory {

    private ServiceStoredQueryTagType $serviceStoredQueryTagType;

    /**
     * StoredQueryFactory constructor.
     * @param ServiceStoredQueryTagType $serviceStoredQueryTagType
     */
    public function __construct(ServiceStoredQueryTagType $serviceStoredQueryTagType) {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
    }

    public function createConsole(?ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);
        $control = new SQLConsole(_('SQL'));
        $container->addComponent($control, 'sql');

        return $container;
    }

    public function createMetadata(?ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $container->addText('name', _('Name'))
            ->addRule(Form::FILLED, _('Název dotazu je třeba vyplnit.'))
            ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 32);

        $container->addText('qid', _('QID'))
            ->setOption('description', _('Dotazy s QIDem nelze smazat a QID lze použít pro práva a trvalé odkazování.'))
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 64)
            ->addRule(Form::PATTERN, _('QID can contain only english letters, numbers and dots.'), '[a-z][a-z0-9.]*');

        $container->addComponent($this->createTagSelect(false, _('Labels'), new StoredQueryTagTypeProvider($this->serviceStoredQueryTagType)), 'tags');

        $container->addTextArea('description', _('Description'));

        $container->addText('php_post_proc', _('PHP post processing'))
            ->setOption('description', _('Název třídy pro zpracování výsledku v PHP. Lze upravit jen v databázi.'))
            ->setDisabled();


        return $container;
    }

    public function createParametersMetadata(?ControlGroup $group = null): Replicator {
        $replicator = new Replicator(function (Container $replContainer) use ($group) {
            $this->buildParameterMetadata($replContainer, $group);

            $submit = $replContainer->addSubmit('remove', _('Remove parameter'));
            $submit->getControlPrototype()->addAttributes(['class' => 'btn-danger btn-sm']);
            $submit->addRemoveOnClick();
        }, 0, true);
        $replicator->containerClass = ModelContainer::class;
        $replicator->setCurrentGroup($group);
        $submit = $replicator->addSubmit('addParam', _('Add parameter'));
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-sm btn-success']);

        $submit->setValidationScope(false)
            ->addCreateOnClick();

        return $replicator;
    }

    private function buildParameterMetadata(Container $container, ControlGroup $group): void {
        $container->setCurrentGroup($group);

        $container->addText('name', _('Parameter name'))
            ->addRule(Form::FILLED, _('Parameter name is required.'))
            ->addRule(Form::MAX_LENGTH, _('Parameter name is too long.'), 16)
            ->addRule(Form::PATTERN, _('Název parametru může být jen z malých písmen anglické abecedy, číslic nebo podtržítka.'), '[a-z][a-z0-9_]*');

        $container->addText('description', _('Description'));

        $container->addSelect('type', _('Data type'))
            ->setItems([
                ModelStoredQueryParameter::TYPE_INT => 'integer',
                ModelStoredQueryParameter::TYPE_STRING => 'string',
                ModelStoredQueryParameter::TYPE_BOOL => 'bool',
            ]);

        $container->addText('default', _('Default value'));
    }

    /**
     * @param ModelStoredQueryParameter[] $queryParameters
     * @param ControlGroup|null $group
     * @return ModelContainer
     * TODO
     */
    public function createParametersValues(array $queryParameters, ?ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        foreach ($queryParameters as $parameter) {
            $name = $parameter->name;
            $subContainer = new ModelContainer();
            $container->addComponent($subContainer, $name);
            // $subcontainer = $container->addContainer($name);

            switch ($parameter->type) {
                case ModelStoredQueryParameter::TYPE_INT:
                case ModelStoredQueryParameter::TYPE_STRING:
                    $valueElement = $subContainer->addText('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    if ($parameter->type == ModelStoredQueryParameter::TYPE_INT) {
                        $valueElement->addRule(Form::INTEGER, _('Parameter %label is numeric.'));
                    }

                    $valueElement->setDefaultValue($parameter->getDefaultValue());
                    break;
                case ModelStoredQueryParameter::TYPE_BOOL:
                    $valueElement = $subContainer->addCheckbox('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    $valueElement->setDefaultValue((bool)$parameter->getDefaultValue());
                    break;
            }
        }

        return $container;
    }

    private function createTagSelect(bool $ajax, string $label, IDataProvider $dataProvider, ?string $renderMethod = null): AutocompleteSelectBox {
        if ($renderMethod === null) {
            $renderMethod = '$("<li>")
                        .append("<a>" + item.label + "<br>" + item.description + ", ID: " + item.value + "</a>")
                        .appendTo(ul);';
        }
        $select = new AutocompleteSelectBox($ajax, $label, $renderMethod);
        $select->setDataProvider($dataProvider);
        $select->setMultiSelect(true);
        return $select;
    }
}
