import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { IApp } from '../app-collector';
import Results from './results/components/';
import Routing from './routing/components/index';
import Schedule from './schedule/components/index';
import Statistics from './statistics/components/';
import TaskCodeApp from './submit-form/components/index';

const registerRouting: IApp = (element, module, component, mode, rawData, actions) => {
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

const registerSubmitForm: IApp = (element, module, component, mode, rawData, actions) => {

    const c = document.createElement('div');
    const {tasks, teams} = JSON.parse(rawData);
    element.appendChild(c);
    ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams} actions={actions}/>, c);
    return true;
};

const registerResults: IApp = (element, module, component, mode, rawData, actions) => {

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

const registerSchedule: IApp = (element, module, component, mode, rawData, actions) => {
    if (!(element instanceof HTMLInputElement)) {
        return false;
    }
    const data = JSON.parse(rawData);

    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    const descriptionElement = element.parentElement.querySelector('span');
    let description = null;
    if (descriptionElement) {
        description = descriptionElement.innerText;
        descriptionElement.style.display = 'none';
    }
    const labelElement = element.parentElement.parentElement.querySelector('label');
    let label = null;
    if (labelElement) {
        label = labelElement.innerHTML;
        labelElement.style.display = 'none';
    }

    if (!(element instanceof HTMLInputElement)) {
        return false;
    }

    element.style.display = 'none';

    ReactDOM.render(<Schedule
        mode={mode}
        actions={actions}
        input={element}
        data={data}
        description={description}
        label={label}
    />, container);
    return true;
};

const registerStatistics: IApp = (element, module, component, mode, rawData, actions) => {

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

export const fyziklani: IApp = (element, module, component, mode, rawData, actions) => {
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
        case 'schedule':
            return registerSchedule(element, module, component, mode, rawData, actions);
        default:
            throw new Error('not implement');
    }

};
