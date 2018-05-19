import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../config';
import { app } from '../reducers';
import PersonProvider from './provider';

interface IProps {
    definitions: any;
}

class App extends React.Component<IProps, {}> {
    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        return (
            <Provider store={store}>
                <PersonProvider accessKey={'person'}/>
            </Provider>
        );
    }
}

const el = document.getElementById('frmform-aggr-person_id');

if (el) {

    ReactDOM.render(<App definitions={{}}/>, el);
}
