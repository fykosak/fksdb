import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { IUploadDataItem } from '../../../shared/interfaces';
import { newDataArrived } from '../../actions/upload-data';
import UploadContainer from './upload-container';

interface IProps {
    data: IUploadDataItem;
}

interface IState {
    onAddSubmits?: (data: IUploadDataItem) => void;
}

class App extends React.Component<IProps & IState, {}> {

    public componentDidMount() {
        const {data, onAddSubmits} = this.props;
        onAddSubmits(data);
    }

    public render() {
        return <UploadContainer/>;
    }
}

const mapStateToProps = (): IState => {
    return {};
};
const mapDispatchToProps = (dispatch: Dispatch<any>): IState => {
    return {
        onAddSubmits: (data: IUploadDataItem) => dispatch(newDataArrived(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(App);
