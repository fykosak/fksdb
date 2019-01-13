import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { App } from '../../app-collector/';
import Index from './components';

export const eventAccommodation: App = (element, module, component, mode, rawData) => {
    if (module !== 'events') {
        return false;
    }
    if (component !== 'accommodation') {
        return false;
    }

    const accommodationDef = JSON.parse(rawData);
    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    if (!(element instanceof HTMLInputElement)) {
        return false;
    }
    element.style.display = 'none';

    ReactDOM.render(<Index accommodationDef={accommodationDef} input={element} mode={mode}/>, container);

    return true;
};
