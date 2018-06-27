import * as React from 'react';
import * as ReactDOM from 'react-dom';
import TaskCodeApp from './components/index';

document.querySelectorAll('#taskcode').forEach((element: HTMLDivElement) => {
    const c = document.createElement('div');
    const tasks = JSON.parse(element.getAttribute('data-tasks'));
    const teams = JSON.parse(element.getAttribute('data-teams'));
    element.appendChild(c);
    ReactDOM.render(<TaskCodeApp tasks={tasks} teams={teams}/>, c);
});
