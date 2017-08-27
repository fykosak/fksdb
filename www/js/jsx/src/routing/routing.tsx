import * as React from 'react';
import logger from 'redux-logger';
import App from './components/app';

import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import {
    IRoom,
    ITeam,
} from './interfaces';
import { app } from './reducers/index';

interface IProps {
    teams: ITeam[];
    rooms: IRoom[];
}

export default class extends React.Component<IProps, {}> {

    public render() {
        // for log events in debug mode
        const store = createStore(app, applyMiddleware(logger));
        // const store = createStore(app);
        const { teams, rooms } = this.props;
        return (
            <Provider store={store}>
                <App teams={teams} rooms={rooms}/>
            </Provider>
        );
    }
}
