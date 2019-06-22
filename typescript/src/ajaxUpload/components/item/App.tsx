import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { newDataArrived } from '../../actions/uploadData';
import UploadContainer from './Container';
import { UploadDataItem } from '../../middleware/UploadDataItem';

interface Props {
    data: UploadDataItem;
}

interface State {
    onAddSubmits?: (data: UploadDataItem) => void;
}

class App extends React.Component<Props & State, {}> {

    public componentDidMount() {
        const {data, onAddSubmits} = this.props;
        onAddSubmits(data);
    }

    public render() {
        const accessKey = '@@submit-api/' + this.props.data.taskId;
        return <UploadContainer accessKey={accessKey}/>;
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
