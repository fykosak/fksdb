import * as React from 'react';
import * as ReactDOM from 'react-dom';

import App from './results-n-stats/components/app';
import Routing from './routing/routing';
import TaskCodeApp from './task-code/components/index';

$('.fyziklani-results').parent('.container').css({ width: 'inherit' });
if (document.getElementsByClassName('fyziklani-results').length) {
    ReactDOM.render(<App/>, document.getElementsByClassName('fyziklani-results')[0]);
}

document.querySelectorAll('#taskcode').forEach((input: HTMLInputElement) => {
    const $ = jQuery;
    if (!input.value) {
        const c = document.createElement('div');
        const tasks = JSON.parse(input.getAttribute('data-tasks'));
        const teams = JSON.parse(input.getAttribute('data-teams'));
        input.parentNode.parentNode.parentNode.appendChild(c);
        $(input).parent().parent().hide();
        ReactDOM.render(<TaskCodeApp node={input} tasks={tasks} teams={teams}/>, c);
    }
});

document.querySelectorAll('.room-edit').forEach((container: HTMLDivElement) => {
    const data = JSON.parse(container.getAttribute('data-data'));
    ReactDOM.render(<Routing teams={data.teams} rooms={data.rooms}/>, container);
});
