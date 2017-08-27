import * as React from 'react';
import logger from 'redux-logger';
import BrawlApp from './brawl-app';

import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import { app } from '../reducers/index';

export default class App extends React.Component<{}, {}> {
    public render() {
        // for log events in debug mode
        const store = createStore(app, applyMiddleware(logger));
        // const store = createStore(app);
        return (
            <Provider store={store}>
                <BrawlApp/>
            </Provider>
        );
    }
}
