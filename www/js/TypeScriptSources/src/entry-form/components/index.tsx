import * as React from 'react';
import logger from 'redux-logger';
import Container from './container';

import { Provider } from 'react-redux';

import {
    applyMiddleware,
    createStore,
} from 'redux';
import { config } from '../../config/index';
import {
    ITask,
    ITeam,
} from '../../shared/interfaces';
import { app } from '../reducers/index';

interface ITaskCodeProps {
    tasks: ITask[];
    teams: ITeam[];
}

export default class TaskCode extends React.Component<ITaskCodeProps, {}> {
    public render() {
        const { tasks, teams } = this.props;
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return (
            <Provider store={store}>
                <Container tasks={tasks} teams={teams}/>
            </Provider>
        );
    }
}
