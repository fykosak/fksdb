import { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/MainComponent';
import ResultsShower from './Components/ResultsShower';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import App from './Components/InnerComponent';
import PositionSwitcher from './Components/PositionSwitcher';
import Setting from './Components/Setting';
import { app } from './Reducers';
import './style.scss';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
}

export default class ResultsPresentationComponent extends React.Component<OwnProps> {
    public render() {
        return <MainComponent actions={this.props.actions} data={this.props.data} app={app}>
            <div className="fyziklani-presentation fixed-top h-100 w-100">
                <Setting/>
                <ResultsShower>
                    <App/>
                    <PositionSwitcher/>
                </ResultsShower>
            </div>
        </MainComponent>;
    }
}
