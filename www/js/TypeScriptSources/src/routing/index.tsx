import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Routing from './components/index';

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
