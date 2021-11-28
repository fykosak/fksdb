import Downloader, { ResponseData } from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import LoadingSwitch from 'FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/LoadingSwitch';
import ActionsStoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/ActionsStoreCreator';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import { Action, Reducer } from 'redux';

interface OwnProps <Store>{
    actions: NetteActions;
    data: ResponseData;
    children: React.ReactNode;
    app: Reducer<Store,Action<string>>;
}

export default class MainComponent<Store> extends React.Component<OwnProps<Store>> {
    public render() {
        const storeMap = {
            actions: this.props.actions,
            data: this.props.data,
            messages: [],
        };
        return (
            <ActionsStoreCreator storeMap={storeMap} app={this.props.app}>
                <div className={'fyziklani-results'}>
                    <Downloader data={this.props.data}/>
                    <LoadingSwitch>
                        {this.props.children}
                    </LoadingSwitch>
                </div>
            </ActionsStoreCreator>
        );
    }
}
