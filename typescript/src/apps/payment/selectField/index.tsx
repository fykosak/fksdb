import {  mapRegister } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import SelectField from './components/selectField';

export const payment = () => {
    mapRegister.register('payment.schedule-select', (element, reactId, rawData, actions) => {

        const items = JSON.parse(rawData);
        const container = document.createElement('div');
        element.parentElement.appendChild(container);
        if (!(element instanceof HTMLInputElement)) {
            return false;
        }
        element.style.display = 'none';
        ReactDOM.render(<SelectField items={items} input={element}/>, container);
    });
};
