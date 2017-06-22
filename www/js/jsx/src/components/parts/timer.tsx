import * as React from 'react';
import {connect} from 'react-redux';
import {getCurrentDelta} from '../../helpers/timer';

interface IProps {
    toStart?: number;
    toEnd?: number;
    visible?: boolean;
    inserted?: Date;
    hardVisible?: boolean;
}

class Timer extends React.Component<IProps, void> {

    componentDidMount() {
        setInterval(() => this.forceUpdate(), 1000);
    }

    public render() {
        const {inserted, visible, toStart, toEnd, hardVisible} = this.props;
        const {currentToStart, currentToEnd} = getCurrentDelta({toStart, toEnd}, inserted);
        let timeStamp = 0;
        if (currentToStart > 0) {
            timeStamp = currentToStart;
        } else if (currentToEnd > 0) {
            timeStamp = currentToEnd;
        } else {
            return null;
        }
        const date = new Date(timeStamp);
        const h = date.getUTCHours();
        const m = date.getUTCMinutes();
        const s = date.getUTCSeconds();
        return (
            <div className={'clock ' + ((visible || hardVisible) ? '' : 'big')}>
                {
                    (h < 10 ? '0' + h : '' + h)
                    + ':' +
                    ( m < 10 ? '0' + m : '' + m)
                    + ':' +
                    (s < 10 ? '0' + s : '' + s)
                }
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        ...state.timer,
        hardVisible: state.options.hardVisible,
    };
};

export default connect(mapStateToProps, null)(Timer);
