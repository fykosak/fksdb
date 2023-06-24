import { app } from 'FKSDB/Components/Schedule/Input/reducer';
import InputConnector2 from './InputConnector2';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/StoreCreator';
import { ModelScheduleGroup } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleGroup';
import * as React from 'react';
import Group from 'FKSDB/Components/Schedule/Input/Components/Group';
import { TranslatorContext } from '@translator/LangContext';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    scheduleDef: {
        group: ModelScheduleGroup;
        options: Params;
    };
    input: HTMLInputElement | HTMLSelectElement;
    translator: Translator<availableLanguage>;
}

export interface Params {
    groupTime: boolean;
    groupLabel: boolean;
    capacity: boolean;
    description: boolean;
    price: boolean;
}

export default class ScheduleField extends React.Component<OwnProps, never> {
    static contextType = TranslatorContext;

    public componentDidMount() {
        this.props.input.style.display = 'none';
        this.props.input.required = false;
        const label = this.props.input.parentElement.getElementsByTagName('label')[0];
        if (label && label instanceof HTMLLabelElement) {
            label.style.display = 'none';
        }
    }

    public render() {
        const translator = this.context;
        const {group, options} = this.props.scheduleDef;
        return <StoreCreator app={app}>
            <TranslatorContext.Provider value={this.props.translator}>
                <InputConnector2 input={this.props.input}/>
                {group
                    ? <Group group={group} params={options}/>
                    : <span className="text-muted">{translator.getText('No items found.')}</span>
                }
            </TranslatorContext.Provider>
        </StoreCreator>;
    }
}
