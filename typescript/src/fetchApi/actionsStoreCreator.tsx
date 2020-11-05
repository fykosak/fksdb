import { Response2 } from '@fetchApi/interfaces';
import StoreCreator from '@shared/components/storeCreator';
import StoreLoader from '@shared/components/storeLoader';
import * as React from 'react';
import {
    PreloadedState,
    Reducer,
} from 'redux';

interface OwnProps {
    storeMap: Response2<any>;
    preloadState?: PreloadedState<any>;
    app: Reducer<any, any>;
}

export default class ActionsStoreCreator extends React.Component<OwnProps, {}> {

    public render() {
        const {storeMap, preloadState, app} = this.props;
        return <StoreCreator app={app} preloadState={preloadState}>
            <StoreLoader storeMap={storeMap}>{this.props.children}</StoreLoader>
        </StoreCreator>;
    }
}
