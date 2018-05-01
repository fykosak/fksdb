import * as React from 'react';
import { connect } from 'react-redux';
import Card from '../../shared/components/card';

import { IStore } from '../reducers';
import UploadForm from './upload-form';
import UploadedFile from './uploaded-file';

interface IState {
    deadline?: string;
    href?: string;
    name?: string;
    submitId?: number;
    taskId?: number;
    submitting?: boolean;
}

class UploadContainer extends React.Component<IState, {}> {

    public render() {

        const {deadline, href, name, submitId, taskId, submitting} = this.props;
        return <div className="col-6 mb-3">
            <Card headline={name + ' - ' + deadline} level={'info'}>
                {submitting ? <div>loading</div> :
                    (submitId ? (
                            <UploadedFile name={name} href={href} submitId={submitId}/>) :
                        <UploadForm data={{deadline, href, name, submitId, taskId}}/>)}

            </Card>
        </div>;
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        deadline: state.uploadData.deadline,
        href: state.uploadData.href,
        name: state.uploadData.name,
        submitId: state.uploadData.submitId,
        submitting: state.submit.submitting,
        taskId: state.uploadData.taskId,
    };
};
const mapDispatchToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(UploadContainer);
