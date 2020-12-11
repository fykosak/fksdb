import * as React from 'react';
import { NetteActions } from '../../../../typescript/appsCollector/netteActions';
import ActionsStoreCreator from '../../../../typescript/fetchApi/actionsStoreCreator';
import UploadContainer from './Components/Container';
import { Submit } from './Middleware';
import { app } from './Reducers/Index';

interface IProps {
    data: Submit;
    actions: NetteActions;
}

export default class AjaxSubmit extends React.Component<IProps, {}> {

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
