import { config } from '@config';
import { lang } from '@i18n/i18n';
import InputConnector from '@inputConnector/compoenents/index';
import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
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
        groupLabel: boolean;
        capacity: boolean;
        description: boolean;
        price: boolean;
    };
}

export default class Index extends React.Component<OwnProps, {}> {

    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return (
            <Provider store={store}>
                <>
                    <InputConnector input={this.props.input}/>
                    {this.getComponentByMode()}
                </>
            </Provider>
        );
    }

    private getComponentByMode(): JSX.Element {
        if (this.props.scheduleDef.groups.length === 0) {
            return <span className="text text-muted">{lang.getText('No items found.')}</span>;
        }
        return <Container groups={this.props.scheduleDef.groups} params={this.props.scheduleDef.options}/>;
    }
}
