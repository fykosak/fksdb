import { ResponseData } from '@apps/fyziklaniResults/downloader/inferfaces';
import MainComponent from '@apps/fyziklaniResults/shared/components/mainComponent';
import { NetteActions } from '@appsCollector/netteActions';
import * as React from 'react';
import ResultsShower from '../shared/components/resultsShower';
import App from './components/app';
import FilterSelect from './components/filters/select';
import { app } from './reducers';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
}

export default class Index extends React.Component<OwnProps, {}> {
    public render() {
        return <MainComponent actions={this.props.actions} data={this.props.data} app={app}>
            <FilterSelect/>
            <ResultsShower>
                <App/>
            </ResultsShower>
        </MainComponent>;
    }
}
