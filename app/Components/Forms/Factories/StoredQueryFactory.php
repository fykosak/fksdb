<?php

namespace FKSDB\Components\Forms\Factories;

use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Controls\Autocomplete\AutocompleteSelectBox;
use FKSDB\Components\Forms\Controls\Autocomplete\IDataProvider;
use FKSDB\Components\Forms\Controls\Autocomplete\StoredQueryTagTypeProvider;
use FKSDB\Components\Forms\Controls\SQLConsole;
use FKSDB\ORM\Models\StoredQuery\ModelStoredQuery;
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

    /**
     * @var ServiceStoredQueryTagType
     */
    private $serviceStoredQueryTagType;

    /**
     * StoredQueryFactory constructor.
     * @param ServiceStoredQueryTagType $serviceStoredQueryTagType
     */
    public function __construct(ServiceStoredQueryTagType $serviceStoredQueryTagType) {
        $this->serviceStoredQueryTagType = $serviceStoredQueryTagType;
    }

    /**
     * @param ControlGroup|null $group
     * @return ModelContainer
     */
    public function createConsole(ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $control = new SQLConsole('SQL');
        $container->addComponent($control, 'sql');

        return $container;
    }

    /**
     * @param ControlGroup|null $group
     * @return ModelContainer
     */
    public function createMetadata(ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        $container->addText('name', _('Název'))
            ->addRule(Form::FILLED, _('Název dotazu je třeba vyplnit.'))
            ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 32);

        $container->addText('qid', _('QID'))
            ->setOption('description', _('Dotazy s QIDem nelze smazat a QID lze použít pro práva a trvalé odkazování.'))
            ->addCondition(Form::FILLED)
            ->addRule(Form::MAX_LENGTH, _('Název dotazu je moc dlouhý.'), 64)
            ->addRule(Form::PATTERN, _('QID může být jen z písmen anglické abecedy a číslic a tečky.'), '[a-z][a-z0-9.]*');

        $container->addComponent($this->createTagSelect(false, _('Štítky'), new StoredQueryTagTypeProvider($this->serviceStoredQueryTagType)), 'tags');

        $container->addTextArea('description', _('Popis dotazu'));

        $container->addText('php_post_proc', _('PHP post processing'))
            ->setOption('description', _('Název třídy pro zpracování výsledku v PHP. Lze upravit jen v databázi.'))
            ->setDisabled();


        return $container;
    }

    /**
     * @param ControlGroup|null $group
     * @return Replicator
     */
    public function createParametersMetadata(ControlGroup $group = null): Replicator {
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

    /**
     * @param Container $container
     * @param mixed $group
     * @internal
     */
    public function buildParameterMetadata(Container $container, ControlGroup $group) {
        $container->setCurrentGroup($group);

        $container->addText('name', _('Název'))
            ->addRule(Form::FILLED, _('Název parametru musí být vyplněn.'))
            ->addRule(Form::MAX_LENGTH, _('Název parametru je moc dlouhý.'), 16)
            ->addRule(Form::PATTERN, _('Název parametru může být jen z malých písmen anglické abecedy, číslic nebo podtržítka.'), '[a-z][a-z0-9_]*');

        $container->addText('description', _('Popis'));

        $container->addSelect('type', _('Datový typ'))
            ->setItems([
                ModelStoredQueryParameter::TYPE_INT => 'integer',
                ModelStoredQueryParameter::TYPE_STRING => 'string',
                ModelStoredQueryParameter::TYPE_BOOL => 'bool',
            ]);

        $container->addText('default', _('Výchozí hodnota'));
    }

    /**
     * @param ModelStoredQuery $queryPattern
     * @param ControlGroup|null $group
     * @return ModelContainer
     */
    public function createParametersValues(ModelStoredQuery $queryPattern, ControlGroup $group = null): ModelContainer {
        $container = new ModelContainer();
        $container->setCurrentGroup($group);

        foreach ($queryPattern->getParameters() as $parameter) {
            $name = $parameter->name;
            $subcontainer = new ModelContainer();
            $container->addComponent($subcontainer, $name);
            // $subcontainer = $container->addContainer($name);

            switch ($parameter->type) {
                case ModelStoredQueryParameter::TYPE_INT:
                case ModelStoredQueryParameter::TYPE_STRING:
                    $valueElement = $subcontainer->addText('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    if ($parameter->type == ModelStoredQueryParameter::TYPE_INT) {
                        $valueElement->addRule(Form::INTEGER, _('Parametr %label je číselný.'));
                    }

                    $valueElement->setDefaultValue($parameter->getDefaultValue());
                    break;
                case ModelStoredQueryParameter::TYPE_BOOL:
                    $valueElement = $subcontainer->addCheckbox('value', $name);
                    $valueElement->setOption('description', $parameter->description);
                    $valueElement->setDefaultValue((bool)$parameter->getDefaultValue());
                    break;
            }
        }

        return $container;
    }

    /**
     * @param bool $ajax
     * @param string $label
     * @param IDataProvider $dataProvider
     * @param string $renderMethod
     * @return AutocompleteSelectBox
     */
    private function createTagSelect(bool $ajax, string $label, IDataProvider $dataProvider, string $renderMethod = null): AutocompleteSelectBox {
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
