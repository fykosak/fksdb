import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { setNextFilter } from '../../actions/table-filter';
import { IStore } from '../../reducers/index';

interface IState {
    onSetNextFilter?: () => any;
}

class Clock extends React.Component<IState, {}> {
    public componentDidMount() {
        const { onSetNextFilter } = this.props;
        setInterval(onSetNextFilter, 30000);
    }

    public render() {
        return null;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onSetNextFilter: () => dispatch(setNextFilter()),
    };
};

export default connect(
    null,
    mapDispatchToProps,
)(Clock);
