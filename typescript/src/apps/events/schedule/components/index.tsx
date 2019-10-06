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

interface Props {
    scheduleDef: ScheduleGroupDef[];
    input: HTMLInputElement;
    mode: string;
}

export interface Params {
    displayGroupLabel: boolean;
    displayCapacity: boolean;
    displayDescription: boolean;
    displayPrice: boolean;
}

export default class Index extends React.Component<Props, {}> {

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
        if (this.props.scheduleDef.length === 0) {
            return <span className="text text-muted">{lang.getText('No items found.')}</span>;
        }
        const params: Params = {
            displayCapacity: true,
            displayDescription: true,
            displayGroupLabel: true,
            displayPrice: true,
        };
        switch (this.props.mode) {
            case 'accommodation':
                break;
            case 'accommodation_teacher_separated':
            case 'accommodation_same_gender_required':
            case 'visa_requirement':
                params.displayCapacity = false;
                params.displayGroupLabel = true;
                params.displayPrice = false;
        }
        return <Container groups={this.props.scheduleDef} params={params}/>;

    }
}
