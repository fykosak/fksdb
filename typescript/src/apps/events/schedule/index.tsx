import { App } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Index from './components';

export const eventSchedule: App = (element, reactId, rawData) => {
    const [module, component, mode] = reactId.split('.');
    if (module !== 'event') {
        return false;
    }
    if (component !== 'schedule') {
        return false;
    }

    const scheduleDef = JSON.parse(rawData);
    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    if (!(element instanceof HTMLInputElement)) {
        return false;
    }
    element.style.display = 'none';

    ReactDOM.render(<Index scheduleDef={scheduleDef} input={element} mode={mode}/>, container);

    return true;
};
