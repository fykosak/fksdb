import ActionsStoreCreator from 'vendor/fykosak/nette-frontend-component/src/Components/ActionsStoreCreator';
import { NetteActions } from 'vendor/fykosak/nette-frontend-component/src/NetteActions/netteActions';
import { ModelSubmit } from 'FKSDB/Models/ORM/Models/modelSubmit';
import * as React from 'react';
import UploadContainer from './Components/Container';
import { app } from './Reducers';
import './style.scss';

interface Props {
    data: ModelSubmit;
    actions: NetteActions;
}

export default class AjaxSubmitComponent extends React.Component<Props> {

    public render() {
        return <ActionsStoreCreator
            initialData={{
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
