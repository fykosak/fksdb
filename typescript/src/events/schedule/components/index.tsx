import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config/';
import InputConnector from '../../../input-connector/compoenents/index';
import { ScheduleGroupDef } from '../middleware/interfaces';
import { app } from '../reducer/';
import Accommodation from './accommodation';

interface Props {
    scheduleDef: ScheduleGroupDef[];
    input: HTMLInputElement;
    mode: string;
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
        switch (this.props.mode) {
            case 'accommodation':
                return <Accommodation scheduleDef={this.props.scheduleDef}/>;
            default:
                throw new Error('no match');
        }

    }
}
