import {
    App,
    NetteActions,
} from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import ParticipantAcquaintance from './participantAcquaintance';

export const charts: App = (element: Element, module: string, component: string, mode: string, rawData: string, actions: NetteActions) => {
    const container = document.querySelector('.container');
    container.classList.remove('container');
    container.classList.add('container-fluid');
    if (module === 'chart') {
        switch (component) {
            case 'participant-acquaintance':
                ReactDOM.render(<ParticipantAcquaintance data={JSON.parse(rawData)}/>, element);
                return true;
        }
    }
    return false;
};
