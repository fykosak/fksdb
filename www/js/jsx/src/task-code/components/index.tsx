import * as React from 'react';
import logger from 'redux-logger';
import Container from './container';

import { Provider } from 'react-redux';

import {
    applyMiddleware,
    createStore,
} from 'redux';
import {
    ITask,
    ITeam,
} from '../middleware/interfaces';
import { app } from '../reducers/index';

interface ITaskCodeProps {
    node: HTMLInputElement;
    tasks: ITask[];
    teams: ITeam[];
}

export default class TaskCode extends React.Component<ITaskCodeProps, {}> {
    public render() {
        const { node, tasks, teams } = this.props;
        // for log events in debug mode
        const store = createStore(app, applyMiddleware(logger));
        // const store = createStore(app);
        return (
            <Provider store={store}>
                <Container tasks={tasks} teams={teams}/>
            </Provider>
        );
    }
}
