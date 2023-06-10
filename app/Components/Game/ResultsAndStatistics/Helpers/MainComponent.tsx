import Downloader, { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/Downloader';
import ActionsStoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/ActionsStoreCreator';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import * as React from 'react';
import { Action, Reducer } from 'redux';

interface OwnProps<Store> {
    actions: NetteActions;
    data: ResponseData;
    children: React.ReactNode;
    app: Reducer<Store, Action<string>>;
}

export default class MainComponent<Store> extends React.Component<OwnProps<Store>> {
    public render() {
        const initialData = {
            actions: this.props.actions,
            data: this.props.data,
            messages: [],
        };
        return (
            <ActionsStoreCreator initialData={initialData} app={this.props.app}>
                <>
                    <Downloader data={this.props.data}/>
                    {this.props.children}
                </>
            </ActionsStoreCreator>
        );
    }
}
