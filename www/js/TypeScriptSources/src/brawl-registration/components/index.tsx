import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
import { config } from '../../config';
import { app } from '../reducers';
import { IDefinitionsState } from '../reducers/definitions';
import Container from './container';

interface IProps {
    definitions: IDefinitionsState;
}

class App extends React.Component<IProps, {}> {
    public render() {
        const store = !config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        return (
            <Provider store={store}>
                <Container definitions={this.props.definitions}/>
            </Provider>
        );
    }
}

const el = document.getElementById('brawl-registration-form');

if (el) {
    const def: IDefinitionsState = {};
    def.accommodation = JSON.parse(el.getAttribute('data-accommodation-def'));
    def.schedule = JSON.parse(el.getAttribute('data-schedule-def'));
    def.persons = JSON.parse(el.getAttribute('data-persons-def'));
    def.studyYears = JSON.parse(el.getAttribute('data-study-years-def'));
    ReactDOM.render(<App definitions={def}/>, el);
}
