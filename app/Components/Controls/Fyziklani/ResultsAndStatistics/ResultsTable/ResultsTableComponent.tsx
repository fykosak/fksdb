import { NetteActions } from '@appsCollector/netteActions';
import { ResponseData } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/shared/components/mainComponent';
import * as React from 'react';
import ResultsShower from '../Helpers/shared/components/resultsShower';
import FilterSelect from './components/filters/select';
import App from './components/Index';
import { app } from './reducers';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
}

export default class ResultsTableComponent extends React.Component<OwnProps, {}> {
    public render() {
        return <MainComponent actions={this.props.actions} data={this.props.data} app={app}>
            <FilterSelect/>
            <ResultsShower>
                <App/>
            </ResultsShower>
        </MainComponent>;
    }
}
