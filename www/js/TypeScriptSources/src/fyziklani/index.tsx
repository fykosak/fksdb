import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Results from './results/components/';
import Routing from './routing/components/index';
import Statistics from './statistics/components/';
import TaskCodeApp from './submit-form/components/index';
import { INetteActions } from '../index';

const registerRouting = (element: Element, mode: string, rawData: string, actions: INetteActions): boolean => {
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

const registerSubmitForm = (element: Element, mode: string, rawData: string, actions: INetteActions): boolean => {

    const c = document.createElement('div');
    const {tasks, teams} = JSON.parse(rawData);
    element.appendChild(c);
    ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams}/>, c);
    return true;
};

const registerResults = (element: Element, mode: string, rawData: string, actions: INetteActions): boolean => {

    switch (mode) {
        case 'presentation':
            element.parentElement.className = 'container-fluid';
            document.querySelectorAll('.breadcrumb')
                .forEach((breadcrumbElement: Element) => {
                    breadcrumbElement.remove();
                });
            document.querySelectorAll('h1')
                .forEach((hElement: Element) => {
                    hElement.remove();
                });
            ReactDOM.render(<Results mode={'presentation'} actions={actions}/>, element);
            return true;
        case 'view':
            ReactDOM.render(<Results mode={'view'} actions={actions}/>, element);
            return true;
        default:
            throw Error('Not implement');
    }
};

const registerStatistics = (element: Element, mode: string, rawData: string, actions: INetteActions): boolean => {

    switch (mode) {
        case 'team':
        case 'task':
            ReactDOM.render(<Statistics mode={mode} actions={actions}/>, element);
            return true;
        default:
            throw Error('Not implement');
    }
};

export const fyziklani = (element: Element, module: string, component: string, mode: string, rawData: string, actions: INetteActions): boolean => {
    if (module !== 'fyziklani') {
        return false;
    }
    switch (component) {
        case 'routing':
            return registerRouting(element, mode, rawData, actions);
        case 'results':
            return registerResults(element, mode, rawData, actions);
        case 'submit-form':
            return registerSubmitForm(element, mode, rawData, actions);
        case 'statistics':
            return registerStatistics(element, mode, rawData, actions);
        default:
            throw new Error('not implement');
    }

};
