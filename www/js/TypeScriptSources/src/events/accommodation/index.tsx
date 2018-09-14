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
    document.querySelectorAll('[data-id=person-accommodation-matrix]').forEach((el: HTMLInputElement) => {
        const accommodationDef = JSON.parse(el.getAttribute('data-accommodation-def'));
        const container = document.createElement('div');
        el.parentElement.parentElement.appendChild(container);
        ReactDOM.render(<Index accommodationDef={accommodationDef} input={el}/>, container);
    });
    return true;
};
