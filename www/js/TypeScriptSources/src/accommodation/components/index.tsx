import * as React from 'react';
import logger from 'redux-logger';

import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import { config } from '../../config/';
import App from './components/app';
import { app } from './reducers/';

interface IProps {

}

class Index extends React.Component<IProps, {}> {

    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const {teams, rooms} = this.props;
        return (
            <Provider store={store}>
                <App teams={teams} rooms={rooms}/>
            </Provider>
        );
    }
}

document.querySelectorAll('[data-id=person-accommodation-matrix]').forEach((el) => {

});
