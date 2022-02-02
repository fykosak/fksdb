import { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/MainComponent';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import App from './Components/Index';
import { app } from './reducers';
import SingleSelect
    from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/ResultsTable/Components/SingleSelect';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
}

export default class ResultsTableComponent extends React.Component<OwnProps> {
    public render() {
        return <MainComponent actions={this.props.actions} data={this.props.data} app={app}>
            <SingleSelect/>
                <App/>
        </MainComponent>;
    }
}
