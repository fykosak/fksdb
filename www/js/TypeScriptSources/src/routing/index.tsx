import * as ReactDOM from 'react-dom';

import * as React from 'react';
import logger from 'redux-logger';

import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import { config } from '../config/';
import {
    IRoom,
    ITeam,
} from '../shared/interfaces';
import App from './components/app';
import { app } from './reducers/';

interface IProps {
    teams: ITeam[];
    rooms: IRoom[];
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

document.querySelectorAll('.room-edit').forEach((container: HTMLDivElement) => {
    const wrap = document.querySelector('#wrap > .container');
    if (wrap) {
        wrap.className = wrap.className.split(' ').reduce((className, name) => {
            if (name === 'container') {
                return className + ' container-fluid';
            }
            return className + ' ' + name;
        }, '');
    }

    const data = JSON.parse(container.getAttribute('data-data'));
    ReactDOM.render(<Index teams={data.teams} rooms={data.rooms}/>, container);
});
