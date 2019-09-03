import { App } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Index from './components/index';

export const eventApplicationsTimeProgress: App = (element, module, component, mode, rawData) => {
    if (module !== 'events') {
        return false;
    }
    if (component !== 'applications-time-progress') {
        return false;
    }

    const data = JSON.parse(rawData);
    const container = document.createElement('div');
    element.appendChild(container);

    ReactDOM.render(<Index data={data}/>, container);

    return true;
};
