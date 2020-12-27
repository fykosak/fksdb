import ActionsStoreCreator from 'FKSDB/Models/FrontEnd/Fetch/ActionsStoreCreator';
import { NetteActions } from 'FKSDB/Models/FrontEnd/Loader/netteActions';
import { ModelSubmit } from 'FKSDB/Models/ORM/Models/modelSubmit';
import * as React from 'react';
import UploadContainer from './Components/Container';
import { app } from './Reducers';
import './style.scss';

interface IProps {
    data: ModelSubmit;
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
