import * as React from 'react';

import logger from 'redux-logger';
import BrawlApp, { IParams } from './app';

import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import { config } from '../../config/index';
import {
    IRoom,
    ITask,
    ITeam,
} from '../../shared/interfaces';
import { app } from '../reducers/index';

interface IProps {
    params: IParams;
    tasks: ITask[];
    teams: ITeam[];
    rooms: IRoom[];
}

export default class Results extends React.Component<IProps, {}> {
    public render() {
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        return (
            <Provider store={store}>
                <BrawlApp
                    teams={this.props.teams}
                    tasks={this.props.tasks}
                    rooms={this.props.rooms}
                    params={this.props.params}
                />
            </Provider>
        );
    }
}
