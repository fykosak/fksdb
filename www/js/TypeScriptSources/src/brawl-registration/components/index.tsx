import * as React from 'react';
import * as ReactDOM from 'react-dom';
import logger from 'redux-logger';

import { Provider } from 'react-redux';

import {
    applyMiddleware,
    createStore,
} from 'redux';
import { config } from '../../config';

import { app } from '../reducers';
import Container from './container';
import { IDefinitionsState } from '../reducers/definitions';

class App extends React.Component<{ def: IDefinitionsState }, {}> {
    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        return (
            <Provider store={store}>
                <Container definitions={this.props.def}/>
            </Provider>
        );
    }
}

const el = document.getElementById('brawl-registration-form');

if (el) {
    const def: IDefinitionsState = {};
    def.accommodation = JSON.parse(el.getAttribute('data-accommodation-def'));
    def.schedule = JSON.parse(el.getAttribute('data-schedule-def'));
    ReactDOM.render(<App def={def}/>, el);
}
