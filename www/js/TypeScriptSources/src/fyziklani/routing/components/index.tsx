import * as React from 'react';
import logger from 'redux-logger';
import App from './app';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import { config } from '../../../config/';
import { app } from '../reducers/';
import {
    IRoom,
    ITeam,
} from '../../helpers/interfaces';

interface IProps {
    teams: ITeam[];
    rooms: IRoom[];
}

export default class extends React.Component<IProps, {}> {

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
