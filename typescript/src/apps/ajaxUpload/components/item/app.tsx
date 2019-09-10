import { NetteActions } from '@appsCollector';
import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { newDataArrived } from '../../actions/uploadData';
import { UploadDataItem } from '../../middleware/uploadDataItem';
import UploadContainer from './container';

interface OwnProps {
    data: UploadDataItem;
    actions: NetteActions;
}

interface DispatchProps {
    onAddSubmits(data: UploadDataItem): void;
}

class App extends React.Component<DispatchProps & OwnProps, {}> {

    public componentDidMount() {
        const {data, onAddSubmits} = this.props;
        onAddSubmits(data);
    }

    public render() {
        const accessKey = '@@submit-api/' + this.props.data.taskId;
        return <UploadContainer actions={this.props.actions} accessKey={accessKey}/>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<any>): DispatchProps => {
    return {
        onAddSubmits: (data: UploadDataItem) => dispatch(newDataArrived(data)),
    };
};

const mapStateToProps = (): {} => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(App);
