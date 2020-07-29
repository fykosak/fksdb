import { mapRegister } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import ItemIndex from './components/item/index';

export const ajaxUpload = () => {
    mapRegister.register('public.ajax-upload', (element, reactId, rawData, actions) => {
        ReactDOM.render(<ItemIndex data={JSON.parse(rawData)} actions={actions}/>, element);
        return true;
    });
};
