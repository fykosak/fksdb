import { mapRegister } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Index from './components';

export const ajaxUpload = () => {
    mapRegister.register('public.ajax-upload', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Index data={JSON.parse(rawData)} actions={actions}/>, element);
        return true;
    });
};
