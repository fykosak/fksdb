import Downloader, { ResponseData } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import LoadingSwitch from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/LoadingSwitch';
import ActionsStoreCreator from '@FKSDB/Model/FrontEnd/Fetch/ActionsStoreCreator';
import { NetteActions } from '@FKSDB/Model/FrontEnd/Loader/netteActions';
import * as React from 'react';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
    children: any;
    app: any;
}

export default class MainComponent extends React.Component<OwnProps, {}> {
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
                        {...this.props.children}
                    </LoadingSwitch>
                </div>
            </ActionsStoreCreator>
        );
    }
}
