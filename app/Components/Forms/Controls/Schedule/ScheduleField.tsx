import { translator } from '@translator/translator';
import Container from 'FKSDB/Components/Forms/Controls/Schedule/Components/Container';
import { app } from 'FKSDB/Components/Forms/Controls/Schedule/reducer';
import NetteInputConnector from 'FKSDB/Models/FrontEnd/InputConnector/NetteInputConnector';
import StoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/StoreCreator';
import { ModelScheduleGroup } from 'FKSDB/Models/ORM/Models/Schedule/modelScheduleGroup';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import './style.scss';
import { mapRegisterCallback } from 'vendor/fykosak/nette-frontend-component/src/Loader/HashMapLoader';

interface OwnProps {
    scheduleDef: {
        groups: ModelScheduleGroup[];
        options: Params;
    };
    input: HTMLInputElement;
    mode: string;
}

export interface Params {
    display: {
        groupTime: boolean;
        groupLabel: boolean;
        capacity: boolean;
        description: boolean;
        price: boolean;
    };
}

class ScheduleField extends React.Component<OwnProps> {
    public componentDidMount() {
        this.props.input.style.display = 'none';
    }

    public render() {
        return <StoreCreator app={app}>
            <>
                <NetteInputConnector input={this.props.input}/>
                {this.getComponentByMode()}
            </>
        </StoreCreator>;
    }

    private getComponentByMode(): JSX.Element {
        if (this.props.scheduleDef.groups.length === 0) {
            return <span className="text text-muted">{translator.getText('No items found.')}</span>;
        }
        return <Container groups={this.props.scheduleDef.groups} params={this.props.scheduleDef.options}/>;
    }
}

export const eventSchedule: mapRegisterCallback = (element, reactId, rawData) => {
    const [module, component, mode] = reactId.split('.');
    if (module !== 'event') {
        return false;
    }
    if (component !== 'schedule') {
        return false;
    }

    const scheduleDef = JSON.parse(rawData);
    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    if (!(element instanceof HTMLInputElement)) {
        return false;
    }
    element.style.display = 'none';

    ReactDOM.render(<ScheduleField scheduleDef={scheduleDef} input={element} mode={mode}/>, container);

    return true;
};
