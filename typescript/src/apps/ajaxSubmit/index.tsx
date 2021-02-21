import UploadContainer from '@apps/ajaxSubmit/components/container';
import { NetteActions } from '@appsCollector/netteActions';
import ActionsStoreCreator from '@fetchApi/actionsStoreCreator';
import * as React from 'react';
import { Submit } from './middleware';
import { app } from './reducers';

interface OwnProps {
    data: Submit;
    actions: NetteActions;
}

export default class Index extends React.Component<OwnProps, {}> {

    public render() {
        return <ActionsStoreCreator
            storeMap={{
                actions: this.props.actions,
                data: this.props.data,
                messages: [],
            }}
            app={app}
        >
            <UploadContainer/>
        </ActionsStoreCreator>;
    }
}
