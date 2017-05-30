import * as React from 'react';
import {connect} from 'react-redux';

interface IProps {
    toStart?: number;
    toEnd?: number;
    visible?: boolean;
}

class Timer extends React.Component<IProps, void> {

    public render() {
        const {toStart, toEnd, visible} = this.props;
        let timeStamp = 0;
        if (toStart > 0) {
            timeStamp = toStart * 1000;
        } else if (toEnd > 0) {
            timeStamp = toEnd * 1000;
        } else {
            return (<div/>);
        }
        const date = new Date(timeStamp);
        const h = date.getUTCHours();
        const m = date.getUTCMinutes();
        const s = date.getUTCSeconds();
        return (
            <div className={'clock '+(visible?'':'big')}>
                {
                    (h < 10 ? "0" + h : "" + h)
                    + ":" +
                    ( m < 10 ? "0" + m : "" + m)
                    + ":" +
                    (s < 10 ? "0" + s : "" + s)
                }
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        ...state.timer,
    }
};

export default connect(mapStateToProps, null)(Timer);
