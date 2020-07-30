import UploadContainer from '@apps/ajaxSubmit/components/container';
import { NetteActions } from '@appsCollector/netteActions';
import ActionsStoreCreator from '@fetchApi/actionsStoreCreator';
import * as React from 'react';
import { Submit } from './middleware';
import { app } from './reducers';

interface IProps {
    data: Submit;
    actions: NetteActions;
}

export default class Index extends React.Component<IProps, {}> {

    public render() {
        const accessKey = '@@submit-api/' + this.props.data.taskId;
        return <ActionsStoreCreator
            actionsMap={{
                [accessKey]: this.props.actions,
            }}
            app={app}
            preloadState={{
                uploadData: {
                    actions: this.props.actions,
                    submit: this.props.data,
                },
            }}>
            <UploadContainer accessKey={accessKey}/>
        </ActionsStoreCreator>;
    }
}
