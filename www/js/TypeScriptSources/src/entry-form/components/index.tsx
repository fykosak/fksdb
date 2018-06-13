import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../config/';
import {
    ITask,
    ITeam,
} from '../../shared/interfaces';
import { app } from '../reducers/';
import Container from './container';

interface ITaskCodeProps {
    tasks: ITask[];
    teams: ITeam[];
}

class Index extends React.Component<ITaskCodeProps, {}> {
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

document.querySelectorAll('#taskcode').forEach((element: HTMLDivElement) => {
    // const c = document.createElement('div');
    const tasks = JSON.parse(element.getAttribute('data-tasks'));
    const teams = JSON.parse(element.getAttribute('data-teams'));
    // element.appendChild(c);
    ReactDOM.render(<Index tasks={tasks} teams={teams}/>, element);
});
