import { NetteActions } from '@appsCollector';
import { config } from '@config';
import * as React from 'react';
import { Provider } from 'react-redux';
import {
    applyMiddleware,
    createStore,
} from 'redux';
import logger from 'redux-logger';
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
        const {actions} = this.props;
        const store = config.dev ? createStore(app, applyMiddleware(logger)) : createStore(app);
        const accessKey = '@@fyziklani-results';
        return (
            <Provider store={store}>
                <div className={'fyziklani-results'}>
                    <Downloader accessKey={accessKey} actions={actions}/>
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
            </Provider>
        );
    }
}
