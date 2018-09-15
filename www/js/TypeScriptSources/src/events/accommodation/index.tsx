import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Index from './components';

export const eventAccommodation = (element: Element, module: string, component: string, mode: string, rawData: string): boolean => {
    if (module !== 'events') {
        return false;
    }
    if (component !== 'accommodation') {
        return false;
    }

    const accommodationDef = JSON.parse(element.getAttribute('data-data'));
    const container = document.createElement('div');
    element.parentElement.parentElement.appendChild(container);
    if (!(element instanceof HTMLInputElement)) {
        return false;
    }
    ReactDOM.render(<Index accommodationDef={accommodationDef} input={element}/>, container);

    return true;
};
