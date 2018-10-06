import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config/';
import { IEventAccommodation } from '../middleware/interfaces';
import { app } from '../reducer/';
import Accommodation from './accommodation';
import InputConnector from './input-connector';

interface IProps {
    accommodationDef: IEventAccommodation[];
    input: HTMLInputElement;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return (
            <Provider store={store}>
                <>
                    <InputConnector input={this.props.input}/>
                    <Accommodation accommodationDef={this.props.accommodationDef}/>
                </>
            </Provider>
        );
    }
}
