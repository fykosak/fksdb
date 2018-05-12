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

class App extends React.Component<{}, {}> {
    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        return (
            <Provider store={store}>
                <Container/>
            </Provider>
        );
    }
}

const el = document.getElementById('brawl-registration-form');

if (el) {
    ReactDOM.render(<App/>, el);

}
