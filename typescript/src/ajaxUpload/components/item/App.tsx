import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { NetteActions } from '../../../app-collector';
import { newDataArrived } from '../../actions/uploadData';
import { UploadDataItem } from '../../middleware/UploadDataItem';
import UploadContainer from './Container';

interface Props {
    data: UploadDataItem;
    actions: NetteActions;
}

interface State {
    onAddSubmits?(data: UploadDataItem): void;
}

class App extends React.Component<Props & State, {}> {

    public componentDidMount() {
        const {data, onAddSubmits} = this.props;
        onAddSubmits(data);
    }

    public render() {
        const accessKey = '@@submit-api/' + this.props.data.taskId;
        return <UploadContainer actions={this.props.actions} accessKey={accessKey}/>;
    }
}

const mapStateToProps = (): State => {
    return {};
};
const mapDispatchToProps = (dispatch: Dispatch<any>): State => {
    return {
        onAddSubmits: (data: UploadDataItem) => dispatch(newDataArrived(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(App);
