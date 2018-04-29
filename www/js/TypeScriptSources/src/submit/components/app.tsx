import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { IUploadData } from '../../shared/interfaces';
import { addUploadSubmits } from '../actions/upload-data';
import UploadContainer from './upload-container';

interface IProps {
    data: IUploadData;
}

interface IState {
    onAddSubmits?: (data: IUploadData) => void;
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
        onAddSubmits: (data: IUploadData) => dispatch(addUploadSubmits(data)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(App);
