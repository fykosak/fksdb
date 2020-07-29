import StoreCreator from '@shared/components/storeCreator';
import { lang } from '@i18n/i18n';
import InputConnector from '@inputConnector/compoenents/index';
import * as React from 'react';
import { ScheduleGroupDef } from '../middleware/interfaces';
import { app } from '../reducer/';
import Container from './container';

interface OwnProps {
    scheduleDef: {
        groups: ScheduleGroupDef[];
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

export default class Index extends React.Component<OwnProps, {}> {

    public render() {
        return <StoreCreator app={app}>
            <>
                <InputConnector input={this.props.input}/>
                {this.getComponentByMode()}
            </>
        </StoreCreator>;
    }

    private getComponentByMode(): JSX.Element {
        if (this.props.scheduleDef.groups.length === 0) {
            return <span className="text text-muted">{lang.getText('No items found.')}</span>;
        }
        return <Container groups={this.props.scheduleDef.groups} params={this.props.scheduleDef.options}/>;
    }
}
