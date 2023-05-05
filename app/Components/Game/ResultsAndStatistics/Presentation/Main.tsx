import { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/Downloader';
import MainComponent from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/MainComponent';
import Toggler from './Components/Toggler';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import App from './Components/InnerComponent';
import PositionSwitcher from './Components/PositionSwitcher';
import Setting from './Components/Setting';
import { app } from '../reducers/store';
import './style.scss';
import CtyrbojTable from 'FKSDB/Components/Game/ResultsAndStatistics/Presentation/Components/CtyrbojTable';
import { availableLanguage, Translator } from '@translator/translator';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
    event: 'fof' | 'ctyrboj';
    translator: Translator<availableLanguage>;
}

export default class Main extends React.Component<OwnProps> {
    public render() {
        return <MainComponent
            actions={this.props.actions}
            data={this.props.data}
            app={app}
            translator={this.props.translator}>
            <div className={'game-presentation fixed-top h-100 w-100 game-' + this.props.event}>
                <Setting/>
                <Toggler event={this.props.event}>
                    {this.props.event === 'fof'
                        ? <>
                            <App/>
                            <PositionSwitcher/>
                        </>
                        : <>
                            <CtyrbojTable/>
                        </>
                    }
                </Toggler>
            </div>
        </MainComponent>;
    }
}
