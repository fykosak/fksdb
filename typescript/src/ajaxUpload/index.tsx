import * as React from 'react';
import * as ReactDOM from 'react-dom';
import {
    App,
    NetteActions,
} from '../app-collector';
import Index from './components/Index';

export const ajaxUpload: App = (element, module, component, mode, rawData, actions: NetteActions) => {
    if (module !== 'public') {
        return false;
    }
    if (component !== 'ajax-upload') {
        return false;
    }

    const container = document.createElement('div');
    element.parentElement.appendChild(container);

    ReactDOM.render(<Index data={JSON.parse(rawData)} actions={actions}/>, element);
    return true;
};
