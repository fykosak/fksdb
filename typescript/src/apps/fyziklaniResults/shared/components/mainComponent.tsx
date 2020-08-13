import Downloader from '@apps/fyziklaniResults/downloader/component';
import { ResponseData } from '@apps/fyziklaniResults/downloader/inferfaces';
import LoadingSwitch from '@apps/fyziklaniResults/shared/components/loadingSwitch';
import { NetteActions } from '@appsCollector/netteActions';
import ActionsStoreCreator from '@fetchApi/actionsStoreCreator';
import * as React from 'react';

interface OwnProps {
    actions: NetteActions;
    data: ResponseData;
    children: any;
    app: any;
}

export const ACCESS_KEY = '@@fyziklani-results';

export default class MainComponent extends React.Component<OwnProps, {}> {
    public render() {
        const storeMap = {
            [ACCESS_KEY]: {
                actions: this.props.actions,
                data: this.props.data,
                messages: [],
            },
        };
        return (
            <ActionsStoreCreator storeMap={storeMap} app={this.props.app}>
                <div className={'fyziklani-results'}>
                    <Downloader accessKey={ACCESS_KEY} data={this.props.data}/>
                    <LoadingSwitch accessKey={ACCESS_KEY}>
                        <>
                            {...this.props.children}
                        </>
                    </LoadingSwitch>
                </div>
            </ActionsStoreCreator>
        );
    }
}
