import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../../config/';
import {
    Room,
    Team,
} from '../../helpers/interfaces';
import { app } from '../reducers/';
import App from './App';

interface Props {
    teams: Team[];
    rooms: Room[];
}

export default class extends React.Component<Props, {}> {

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
