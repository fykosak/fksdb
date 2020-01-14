import { App } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Results from './results/components/';
import Routing from './routing/components/';
import Statistics from './statistics/components/';
import TaskCodeApp from './submitForm/components/';

const registerRouting: App = (element, module, component, mode, rawData) => {
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
    return true;
};

const registerSubmitForm: App = (element, module, component, mode, rawData, actions) => {

    const c = document.createElement('div');
    const {tasks, teams, availablePoints} = JSON.parse(rawData);
    element.appendChild(c);
    ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams} actions={actions} availablePoints={availablePoints}/>, c);
    return true;
};

const registerResults: App = (element, module, component, mode, rawData, actions) => {

    switch (mode) {
        case 'presentation':
            /* element.parentElement.className = 'container-fluid';
             document.querySelectorAll('.breadcrumb')
                 .forEach((breadcrumbElement: Element) => {
                     breadcrumbElement.remove();
                 });
             document.querySelectorAll('h1')
                 .forEach((hElement: Element) => {
                     hElement.remove();
                 });*/
            ReactDOM.render(<Results mode={'presentation'} actions={actions}/>, element);
            return true;
        case 'view':
            ReactDOM.render(<Results mode={'view'} actions={actions}/>, element);
            return true;
        default:
            throw Error('Not implement');
    }
};

const registerStatistics: App = (element, module, component, mode, rawData, actions) => {

    switch (mode) {
        case 'team':
        case 'task':
        case 'correlation':
            ReactDOM.render(<Statistics mode={mode} actions={actions}/>, element);
            return true;
        default:
            throw Error('Not implement');
    }
};

export const fyziklani: App = (element, module, component, mode, rawData, actions) => {
    if (module !== 'fyziklani') {
        return false;
    }
    switch (component) {
        case 'routing':
            return registerRouting(element, module, component, mode, rawData, actions);
        case 'results':
            return registerResults(element, module, component, mode, rawData, actions);
        case 'submit-form':
            return registerSubmitForm(element, module, component, mode, rawData, actions);
        case 'statistics':
            return registerStatistics(element, module, component, mode, rawData, actions);
        default:
            throw new Error('not implement');
    }

};
