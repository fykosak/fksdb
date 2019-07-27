import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { NetteActions } from '../../../app-collector';
import { config } from '../../../config/';
import {
    Task,
    Team,
} from '../../helpers/interfaces/';
import { app } from '../reducers/';
import Container from './Container';

interface Props {
    tasks: Task[];
    teams: Team[];
    actions: NetteActions;
    availablePoints: number[];
}

export default class TaskCode extends React.Component<Props, {}> {
    public render() {
        const {tasks, teams, actions, availablePoints} = this.props;
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);

        return (
            <Provider store={store}>
                <Container tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>
            </Provider>
        );
    }
}
