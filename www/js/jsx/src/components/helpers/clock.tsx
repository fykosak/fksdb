import * as React from 'react';
import {connect} from 'react-redux';
import {
    tick,
} from '../../actions/tick';

interface IProps {
    onTick?: Function;
}

class Clock extends React.Component<IProps,any> {
    public componentDidMount() {
        const {onTick} = this.props;
       // setInterval(onTick, 1000);
    }

    public render() {
        return (
            <div/>
        );
    };
}

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onTick: () => dispatch(tick()),
    };
};

export default connect(
    null,
    mapDispatchToProps,
)(Clock);
