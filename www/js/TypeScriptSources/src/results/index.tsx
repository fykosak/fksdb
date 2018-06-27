import * as React from 'react';
import * as ReactDOM from 'react-dom';

import Results from './components/index';

document.querySelectorAll('.brawl-results')
    .forEach((element: Element) => {
        const params = JSON.parse(element.getAttribute('data-params'));
        const teams = JSON.parse(element.getAttribute('data-teams'));
        const tasks = JSON.parse(element.getAttribute('data-tasks'));
        const rooms = JSON.parse(element.getAttribute('data-rooms'));

        element.parentElement.className = 'container-fluid';
        document.querySelectorAll('.breadcrumb')
            .forEach((breadcrumbElement: Element) => {
                breadcrumbElement.remove();
            });
        document.querySelectorAll('h1')
            .forEach((hElement: Element) => {
                hElement.remove();
            });
        ReactDOM.render(<Results params={params} teams={teams} tasks={tasks} rooms={rooms}/>, element);
    });
