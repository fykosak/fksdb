import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import InputConnector from '../../../input-connector/compoenents/index';
import { IPaymentAccommodationItem } from '../interfaces';
import { app } from '../reducer/';
import SelectContainer from './select-container';

interface IProps {
    items: IPaymentAccommodationItem[];
    input: HTMLInputElement;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        // const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const store = createStore(app, applyMiddleware(logger));
        return (
            <Provider store={store}>
                <>
                    <InputConnector input={this.props.input}/>
                    <SelectContainer items={this.props.items}/>
                </>
            </Provider>
        );
    }
}
