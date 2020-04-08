import { mapRegister } from '@appsCollector';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Presentation from './presentation/components';
import Statistics from './statistics/components';
import Table from './table/components';

export const fyziklaniResults = () => {
    mapRegister.register('fyziklani.statistics.team', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Statistics mode={'team'} actions={actions}/>, element);
    });
    mapRegister.register('fyziklani.statistics.task', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Statistics mode={'task'} actions={actions}/>, element);
    });
    mapRegister.register('fyziklani.statistics.correlation', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Statistics mode={'correlation'} actions={actions}/>, element);
    });
    mapRegister.register('fyziklani.results.presentation', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Presentation actions={actions}/>, element);
    });
    mapRegister.register('fyziklani.results.table', (element, reactId, rawData, actions) => {
        ReactDOM.render(<Table actions={actions}/>, element);
    });
};
