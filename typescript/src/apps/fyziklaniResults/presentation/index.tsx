import { ResponseData } from '@apps/fyziklaniResults/downloader/inferfaces';
import MainComponent from '@apps/fyziklaniResults/shared/components/mainComponent';
import { NetteActions } from '@appsCollector/netteActions';
import * as React from 'react';
import ResultsShower from '../shared/components/resultsShower';
import App from './components/app';
import PositionSwitcher from './components/positionSwitcher';
import Settings from './components/settings';
import { app } from './reducers';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
}

export default class Index extends React.Component<OwnProps, {}> {
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
