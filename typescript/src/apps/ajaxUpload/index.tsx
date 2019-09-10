import {
    App,
    NetteActions,
} from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Index from './components';

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
