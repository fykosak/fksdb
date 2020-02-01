import { mapRegister } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Index from './components/index';

export const eventApplicationsTimeProgress = () => {
    mapRegister.register('events.applications-time-progress.participants', (element, reactId, rawData, actions) => {
        const data = JSON.parse(rawData);
        const container = document.createElement('div');
        element.appendChild(container);
        ReactDOM.render(<Index data={data} accessKey={'participants'}/>, container);
    });
    mapRegister.register('events.applications-time-progress.teams', (element, reactId, rawData, actions) => {
        const data = JSON.parse(rawData);
        const container = document.createElement('div');
        element.appendChild(container);
        ReactDOM.render(<Index data={data} accessKey={'teams'}/>, container);
    });

};
