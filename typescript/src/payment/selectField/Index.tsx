import * as React from 'react';
import * as ReactDOM from 'react-dom';
import { App } from '../../app-collector/';
import SelectField from './components/SelectField';

export const payment: App = (element, module, component, mode, rawData) => {
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

    ReactDOM.render(<SelectField items={items} input={element}/>, container);

    return true;
};
