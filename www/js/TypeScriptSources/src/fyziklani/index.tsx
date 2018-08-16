import * as React from 'react';
import * as ReactDOM from 'react-dom';
import ResultsApp from './results/components/';
import Statistics from './statistics/components/';

export const fyziklani = () => {

    document.querySelectorAll('.brawl-results').forEach((element: Element) => {

        element.parentElement.className = 'container-fluid';
        document.querySelectorAll('.breadcrumb')
            .forEach((breadcrumbElement: Element) => {
                breadcrumbElement.remove();
            });
        document.querySelectorAll('h1')
            .forEach((hElement: Element) => {
                hElement.remove();
            });
        switch (element.getAttribute('data-type')) {
            case 'results':
                ReactDOM.render(<ResultsApp/>, element);
                break;
            case 'statistics':
                ReactDOM.render(<Statistics/>, element);
                break;
            default:
                throw Error('Not implement');
        }
    });

};
