import * as React from 'react';
import * as ReactDOM from 'react-dom';
import Results from './results/components/';
import Statistics from './statistics/components/';

export const fyziklani = () => {

    document.querySelectorAll('.brawl-results').forEach((element: Element) => {


        switch (element.getAttribute('data-mode')) {
            case 'results-presentation':
                element.parentElement.className = 'container-fluid';
                document.querySelectorAll('.breadcrumb')
                    .forEach((breadcrumbElement: Element) => {
                        breadcrumbElement.remove();
                    });
                document.querySelectorAll('h1')
                    .forEach((hElement: Element) => {
                        hElement.remove();
                    });
                ReactDOM.render(<Results mode={'presentation'}/>, element);
                break;
            case 'results-view':
                ReactDOM.render(<Results mode={'view'}/>, element);
                break;
            case 'team-statistics':
                ReactDOM.render(<Statistics mode={'team'}/>, element);
                break;
            case 'task-statistics':
                ReactDOM.render(<Statistics mode={'task'}/>, element);
                break;
            default:
                throw Error('Not implement');
        }
    });

};
