import * as React from 'react';
import {Provider} from 'react-redux';
import {
    createStore,
    applyMiddleware,
} from 'redux';
import logger from 'redux-logger'
import {app} from '../reducers/index';

import FyziklaniApp from './fyziklani-app';

export default class App extends React.Component<any, any> {
    public render() {
        // for log events in debug mode
        //const store = createStore(app, applyMiddleware(logger));
        const store = createStore(app);
        return (
            <Provider store={store}>
                <FyziklaniApp/>
            </Provider>
        );
    }
}
