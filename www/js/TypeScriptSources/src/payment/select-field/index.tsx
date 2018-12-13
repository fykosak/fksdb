import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { IApp } from '../../app-collector/';
import Index from './components';

export const payment: IApp = (element, module, component, mode, rawData) => {
    if (module !== 'payment') {
        return false;
    }
    if (component !== 'accommodation-select') {
        return false;
    }

    const items = JSON.parse(rawData);
    const container = document.createElement('div');
    element.parentElement.appendChild(container);
    if (!(element instanceof HTMLInputElement)) {
        return false;
    }
    element.style.display = 'none';

    ReactDOM.render(<Index items={items} input={element}/>, container);

    return true;
};
