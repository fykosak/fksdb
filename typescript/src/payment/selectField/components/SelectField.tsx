import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config';
import InputConnector from '../../../input-connector/compoenents/Index';
import { PaymentAccommodationItem } from '../interfaces';
import { app } from '../reducer/';
import Container from './Container';

interface Props {
    items: PaymentAccommodationItem[];
    input: HTMLInputElement;
}

export default class SelectField extends React.Component<Props, {}> {

    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        return (
            <Provider store={store}>
                <>
                    <InputConnector input={this.props.input}/>
                    <Container items={this.props.items}/>
                </>
            </Provider>
        );
    }
}
