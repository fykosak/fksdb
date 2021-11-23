import { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/MainComponent';
import ResultsShower from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/ResultsShower';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import FilterSelect from './components/filters/select/Index';
import App from './components/Index';
import { app } from './reducers';
import './style.scss';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
}

export default class ResultsTableComponent extends React.Component<OwnProps, Record<string, never>> {
    public render() {
        return <MainComponent actions={this.props.actions} data={this.props.data} app={app}>
            <FilterSelect/>
            <ResultsShower>
                <App/>
            </ResultsShower>
        </MainComponent>;
    }
}
