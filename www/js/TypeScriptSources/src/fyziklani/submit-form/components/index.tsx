import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { INetteActions } from '../../../app-collector';
import { config } from '../../../config/';
import {
    ITask,
    ITeam,
} from '../../helpers/interfaces/';
import { app } from '../reducers/';
import Container from './container';

interface ITaskCodeProps {
    tasks: ITask[];
    teams: ITeam[];
    actions: INetteActions;
    availablePoints: number[];
}

export default class TaskCode extends React.Component<ITaskCodeProps, {}> {
    public render() {
        const {tasks, teams, actions, availablePoints} = this.props;
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return (
            <Provider store={store}>
                <Container availablePoints={availablePoints} tasks={tasks} teams={teams} actions={actions}/>
            </Provider>
        );
    }
}
