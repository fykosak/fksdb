import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Results from './results/components/';
import Routing from './routing/components/index';
import Statistics from './statistics/components/';
import TaskCodeApp from './submit-form/components/index';

const registerRouting = () => {
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
        ReactDOM.render(<Routing teams={data.teams} rooms={data.rooms}/>, container);
    });
};

const registerSubmitForm = () => {
    document.querySelectorAll('#taskcode').forEach((element: HTMLDivElement) => {
        const c = document.createElement('div');
        const tasks = JSON.parse(element.getAttribute('data-tasks'));
        const teams = JSON.parse(element.getAttribute('data-teams'));
        element.appendChild(c);
        ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams}/>, c);
    });
};

export const fyziklani = () => {

    document.querySelectorAll('.brawl-results').forEach((element: Element) => {

        switch (element.getAttribute('data-mode')) {
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
                break;
            case 'results-view':
                ReactDOM.render(<Results mode={'view'}/>, element);
                break;
            case 'team-statistics':
                ReactDOM.render(<Statistics mode={'team'}/>, element);
                break;
            case 'task-statistics':
                ReactDOM.render(<Statistics mode={'task'}/>, element);
                break;
            default:
                throw Error('Not implement');
        }
    });
    registerRouting();
    registerSubmitForm();
};
