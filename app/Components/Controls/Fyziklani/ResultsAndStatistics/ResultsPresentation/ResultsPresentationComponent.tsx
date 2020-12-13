import { ResponseData } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/MainComponent';
import ResultsShower from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/ResultsShower';
import { NetteActions } from '@FKSDB/Model/FrontEnd/Loader/netteActions';
import * as React from 'react';
import App from './Components/InnerComponent';
import PositionSwitcher from './Components/PositionSwitcher';
import Settings from './Components/Settings/Form';
import { app } from './Reducers';
import './style.scss';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
}

export default class ResultsPresentationComponent extends React.Component<OwnProps, {}> {
    public render() {
        return <MainComponent actions={this.props.actions} data={this.props.data} app={app}>
            <Settings/>
            <div className={'fixed-top h-100 w-100'} data-toggle="modal"
                 data-target="#fyziklaniResultsOptionModal">
                <ResultsShower className={'inner-headline h-100 w-100'}>
                    <App/>
                    <PositionSwitcher/>
                </ResultsShower>
            </div>
        </MainComponent>;
    }
}
