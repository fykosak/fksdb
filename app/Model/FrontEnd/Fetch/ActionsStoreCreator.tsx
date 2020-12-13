import { Response2 } from '@FKSDB/Model/FrontEnd/Fetch/interfaces';
import StoreCreator from '@FKSDB/Model/FrontEnd/Loader/StoreCreator';
import StoreLoader from '@FKSDB/Model/FrontEnd/Loader/StoreLoader';
import * as React from 'react';
import { Reducer } from 'redux';

interface OwnProps {
    storeMap: Response2<any>;
    app: Reducer<any, any>;
}

export default class ActionsStoreCreator extends React.Component<OwnProps, {}> {

    public render() {
        const {storeMap, app} = this.props;
        return <StoreCreator app={app}>
            <StoreLoader storeMap={storeMap}>{this.props.children}</StoreLoader>
        </StoreCreator>;
    }
}
