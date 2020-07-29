import { NetteActions } from '@appsCollector/netteActions';
import ActionsStoreCreator from '@fetchApi/components/actionsStoreCreator';
import * as React from 'react';
import Downloader from '../../downloader/components';
import LoadingSwitch from '../../shared/components/loadingSwitch';
import ResultsShower from '../../shared/components/resultsShower';
import { app } from '../reducers';
import App from './app';
import FilterSelect from './filters/select';

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
                            <FilterSelect/>
                            <ResultsShower className={null}>
                                <App/>
                            </ResultsShower>
                        </>
                    </LoadingSwitch>
                </div>
            </ActionsStoreCreator>
        );
    }
}
