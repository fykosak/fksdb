import { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/MainComponent';
import Toggler from './Components/Toggler';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import App from './Components/InnerComponent';
import PositionSwitcher from './Components/PositionSwitcher';
import Setting from './Components/Setting';
import { app } from './reducers';
import './style.scss';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
    event: 'fof' | 'ctyrboj';
}

export default class Main extends React.Component<OwnProps> {
    public render() {
        return <MainComponent actions={this.props.actions} data={this.props.data} app={app}>
            <div className={'game-presentation fixed-top h-100 w-100 game-' + this.props.event}>
                <Setting/>
                <Toggler event={this.props.event}>
                    <App/>
                    <PositionSwitcher/>
                </Toggler>
            </div>
        </MainComponent>;
    }
}
