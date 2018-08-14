import * as React from 'react';
import { connect } from 'react-redux';
import Card from '../../../shared/components/card';

import { IStore } from '../../reducers';
import File from './states/file';
import Form from './states/form';

import MessagesDisplay from '../../../shared/components/messages-display';

interface IProps {
    accessKey: string;
}

interface IState {
    deadline?: string;
    href?: string;
    name?: string;
    submitId?: number;
    taskId?: number;
    submitting?: boolean;
}

class UploadContainer extends React.Component<IState & IProps, {}> {

    public render() {

        const {deadline, href, name, submitId, taskId, submitting} = this.props;
        const headline = (<>
            <h4>{name}</h4>
            <small className="text-muted">{deadline}</small>
        </>);
        const {accessKey} = this.props;
        return <div className="col-md-6 mb-3">
            <Card headline={headline} level={'info'}>
                <MessagesDisplay accessKey={accessKey}/>
                {submitting ? (<div className="text-center">
                        <span className="d-block">Loading</span>
                        <span className="display-1 d-block"><i className="fa fa-spinner fa-spin "/></span>
                    </div>) :
                    (submitId ?
                            (<File accessKey={accessKey} name={name} href={href} submitId={submitId}/>) :
                            (<Form accessKey={accessKey} data={{deadline, href, name, submitId, taskId}}/>)
                    )
                }
            </Card>
        </div>;
    }
}

const mapStateToProps = (state: IStore): IState => {
    const values = {
        submitting: false,
    };
    const accessKey = '@@submit-api/' + state.uploadData.taskId;
    if (state.fetchApi.hasOwnProperty(accessKey)) {
        values.submitting = state.fetchApi[accessKey].submitting;
    }
    return {
        deadline: state.uploadData.deadline,
        href: state.uploadData.href,
        name: state.uploadData.name,
        submitId: state.uploadData.submitId,
        taskId: state.uploadData.taskId,
        ...values,
    };
};
const mapDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(UploadContainer);
