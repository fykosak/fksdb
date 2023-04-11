import { translator } from '@translator/translator';
import { app } from 'FKSDB/Components/Forms/Controls/Schedule/reducer';
import InputConnector2 from './InputConnector2';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/StoreCreator';
import { ModelScheduleGroup } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleGroup';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { mapRegisterCallback } from 'vendor/fykosak/nette-frontend-component/src/Loader/HashMapLoader';
import Group from 'FKSDB/Components/Forms/Controls/Schedule/Components/Group';

interface OwnProps {
    scheduleDef: {
        group: ModelScheduleGroup;
        options: Params;
    };
    input: HTMLInputElement | HTMLSelectElement;
}

export interface Params {
    groupTime: boolean;
    groupLabel: boolean;
    capacity: boolean;
    description: boolean;
    price: boolean;
}

class ScheduleField extends React.Component<OwnProps> {
    public componentDidMount() {
        this.props.input.style.display = 'none';
        this.props.input.required = false;
        const label = this.props.input.parentElement.getElementsByTagName('label')[0];
        if (label && label instanceof HTMLLabelElement) {
            label.style.display = 'none';
        }
    }

    public render() {
        const {group, options} = this.props.scheduleDef;
        return <StoreCreator app={app}>
            <>
                <InputConnector2 input={this.props.input}/>
                {group
                    ? <Group group={group} params={options}/>
                    : <span className="text-muted">{translator.getText('No items found.')}</span>
                }
            </>
        </StoreCreator>;
    }
}

export const eventSchedule: mapRegisterCallback = (element, reactId, rawData) => {
    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    if (element instanceof HTMLInputElement || element instanceof HTMLSelectElement) {
        ReactDOM.render(<ScheduleField scheduleDef={JSON.parse(rawData)} input={element}/>, container);
        return true;
    }
    return false;
};
