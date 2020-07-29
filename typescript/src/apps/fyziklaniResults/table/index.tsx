import { NetteActions } from '@appsCollector/netteActions';
import ActionsStoreCreator from '@fetchApi/components/actionsStoreCreator';
import * as React from 'react';
import Downloader from '../downloader/components';
import LoadingSwitch from '../shared/components/loadingSwitch';
import ResultsShower from '../shared/components/resultsShower';
import App from './components/app';
import FilterSelect from './components/filters/select';
import { app } from './reducers';

interface OwnProps {
    actions: NetteActions;
    data: any;
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
