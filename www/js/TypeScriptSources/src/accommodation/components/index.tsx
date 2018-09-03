import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../config/';
import { IEventAccommodation } from '../middleware/interfaces';
import { app } from '../reducer/';
import Accommodation from './accommodation';
import InputConnector from './input-connector';

interface IProps {
    accommodationDef: IEventAccommodation[];
    input: HTMLInputElement;
}

class Index extends React.Component<IProps, {}> {

    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

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

document.querySelectorAll('[data-id=person-accommodation-matrix]').forEach((el: HTMLInputElement) => {
    const accommodationDef = JSON.parse(el.getAttribute('data-accommodation-def'));
    const container = document.createElement('div');
    el.parentElement.parentElement.appendChild(container);
    ReactDOM.render(<Index accommodationDef={accommodationDef} input={el}/>, container);
});
