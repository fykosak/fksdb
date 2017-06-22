import * as React from 'react';
import {connect} from 'react-redux';
import {
    setNextFilter,
} from '../../actions/table-filter';

interface IProps {
    onSetNextFilter?: Function;
}

class Clock extends React.Component<IProps,any> {
    public componentDidMount() {
        const {onSetNextFilter} = this.props;
        setInterval(onSetNextFilter, 30000);
    }

    public render() {
        return null;
    };
}

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onSetNextFilter: () => dispatch(setNextFilter()),
    };
};

export default connect(
    null,
    mapDispatchToProps,
)(Clock);
