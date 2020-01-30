import { mapRegister } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Results from './results/components/';
import Routing from './routing/components/';
import Statistics from './statistics/components/';
import TaskCodeApp from './submitForm/components/';

export const fyziklani = () => {
    mapRegister.register('fyziklani.statistics.team', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Statistics mode={'team'} actions={actions}/>, element);
    });
    mapRegister.register('fyziklani.statistics.task', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Statistics mode={'task'} actions={actions}/>, element);
    });
    mapRegister.register('fyziklani.statistics.correlation', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Statistics mode={'correlation'} actions={actions}/>, element);
    });
    mapRegister.register('fyziklani.results.presentation', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Results mode={'presentation'} actions={actions}/>, element);
    });
    mapRegister.register('fyziklani.results.view', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Results mode={'view'} actions={actions}/>, element);
    });

    mapRegister.register('fyziklani.submit-form', (element, reactId, rawData, actions) => {
        const c = document.createElement('div');
        const {tasks, teams, availablePoints} = JSON.parse(rawData);
        element.appendChild(c);
        ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams} actions={actions}
                                     availablePoints={availablePoints}/>, c);
    });
    mapRegister.register('fyziklani.routing', (element, reactId, rawData, actions) => {
        const wrap = document.querySelector('#wrap > .container');
        if (wrap) {
            wrap.className = wrap.className.split(' ').reduce((className, name) => {
                if (name === 'container') {
                    return className + ' container-fluid';
                }
                return className + ' ' + name;
            }, '');
        }
        const data = JSON.parse(rawData);
        ReactDOM.render(<Routing teams={data.teams} rooms={data.rooms}/>, element);
    });

};
