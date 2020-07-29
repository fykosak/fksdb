import { NetteActions } from '@appsCollector/netteActions';
import ActionsStoreCreator from '@fetchApi/components/actionsStoreCreator';
import * as React from 'react';
import Downloader from '../../downloader/components';
import LoadingSwitch from '../../shared/components/loadingSwitch';
import ResultsShower from '../../shared/components/resultsShower';
import { app } from '../reducers';
import App from './app';
import PositionSwitcher from './positionSwitcher';
import Settings from './settings';

interface OwnProps {
    actions: NetteActions;
}

export default class Index extends React.Component<OwnProps, {}> {
    public render() {
        const accessKey = '@@fyziklani-results';
        return (
            <ActionsStoreCreator actionsMap={{[accessKey]: this.props.actions}} app={app}>
                <div className={'fyziklani-results'}>
                    <Downloader accessKey={accessKey}/>
                    <LoadingSwitch>
                        <>
                            <Settings/>
                            <div className={'fixed-top h-100 w-100'} data-toggle="modal"
                                 data-target="#fyziklaniResultsOptionModal">
                                <ResultsShower className={'inner-headline h-100 w-100'}>
                                    <App/>
                                    <PositionSwitcher/>
                                </ResultsShower>
                            </div>
                        </>
                    </LoadingSwitch>
                </div>
            </ActionsStoreCreator>
        );
    }
}
