import { NetteActions } from '@appsCollector/netteActions';
import StoreCreator from '@shared/components/storeCreator';
import * as React from 'react';
import {
    PreloadedState,
    Reducer,
} from 'redux';

interface OwnProps {
    actionsMap: {
        [accessKey: string]: NetteActions;
    };
    preloadState?: PreloadedState<any>;
    app: Reducer<any, any>;
}

export default class ActionsStoreCreator extends React.Component<OwnProps, {}> {
    public render() {
        const {actionsMap, preloadState, app} = this.props;
        const state = {
            fetchApi: {},
            ...preloadState,
        };
        for (const accessKey in actionsMap) {
            if (actionsMap.hasOwnProperty(accessKey)) {
                state.fetchApi[accessKey] = {
                    actions: actionsMap[accessKey],
                    error: null,
                    messages: [],
                    submitting: false,
                };
            }
        }
        return <StoreCreator app={app} preloadState={state}>{this.props.children}</StoreCreator>;
    }
}
