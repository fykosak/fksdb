import { UploadDataItem } from '@apps/ajaxUpload/middleware/uploadDataItem';
import { NetteActions } from '@appsCollector';
import Card from '@shared/components/card';
import * as React from 'react';
import { connect } from 'react-redux';
import { Store } from '../../reducers';
import MessageBox from '../messageBox';
import File from './states/file';
import Form from './states/form';

interface OwnProps {
    accessKey: string;
    actions: NetteActions;
}

interface StateProps {
    submitting: boolean;
    submit: UploadDataItem;
}

class UploadContainer extends React.Component<OwnProps & StateProps, {}> {

    public render() {

        const {submit, submit: {deadline, name, submitId}, submitting, actions} = this.props;
        const headline = (<>
            <h4>{name}</h4>
            <small className="text-muted">{deadline}</small>
        </>);
        const {accessKey} = this.props;
        return <div className="col-md-6 mb-3">
            <Card headline={headline} level={'info'}>
                <MessageBox accessKey={accessKey}/>
                {submitting ? (<div className="text-center">
                        <span className="d-block">Loading</span>
                        <span className="display-1 d-block"><i className="fa fa-spinner fa-spin "/></span>
                    </div>) :
                    (submitId ?
                            (<File actions={actions} accessKey={accessKey} submit={submit}/>) :
                            (<Form actions={actions} accessKey={accessKey} submit={submit}/>)
                    )
                }
            </Card>
        </div>;
    }
}

const mapStateToProps = (state: Store, ownProps: OwnProps): StateProps => {
    const {accessKey} = ownProps;
    return {
        submit: {
            ...state.uploadData,
        },
        submitting: state.fetchApi.hasOwnProperty(accessKey) ? state.fetchApi[accessKey].submitting : false,
    };
};

export default connect(mapStateToProps, null)(UploadContainer);
