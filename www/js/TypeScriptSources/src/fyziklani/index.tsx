import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Results from './results/components/';
import Routing from './routing/components/index';
import Statistics from './statistics/components/';
import TaskCodeApp from './submit-form/components/index';

const registerRouting = (element: Element, mode: string, rawData: string): boolean => {
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

const registerSubmitForm = (element: Element, mode: string, rawData: string): boolean => {

    const c = document.createElement('div');
    const {tasks, teams} = JSON.parse(rawData);
    element.appendChild(c);
    ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams}/>, c);
    return true;
};

const registerResults = (element: Element, mode: string, rawData: string): boolean => {

    switch (mode) {
        case 'results-presentation':
            element.parentElement.className = 'container-fluid';
            document.querySelectorAll('.breadcrumb')
                .forEach((breadcrumbElement: Element) => {
                    breadcrumbElement.remove();
                });
            document.querySelectorAll('h1')
                .forEach((hElement: Element) => {
                    hElement.remove();
                });
            ReactDOM.render(<Results mode={'presentation'}/>, element);
            return true;
        case 'results-view':
            ReactDOM.render(<Results mode={'view'}/>, element);
            return true;
        case 'team-statistics':
            ReactDOM.render(<Statistics mode={'team'}/>, element);
            return true;
        case 'task-statistics':
            ReactDOM.render(<Statistics mode={'task'}/>, element);
            return true;
        default:
            throw Error('Not implement');
    }
};

export const fyziklani = (element: Element, module: string, component: string, mode: string, rawData: string): boolean => {
    if (module !== 'fyziklani') {
        return false;
    }
    switch (component) {
        case 'routing':
            return registerRouting(element, mode, rawData);
        case 'results':
            return registerResults(element, mode, rawData);
        case 'submit-form':
            return registerSubmitForm(element, mode, rawData);
        default:
            throw new Error('not implement');
    }

};
