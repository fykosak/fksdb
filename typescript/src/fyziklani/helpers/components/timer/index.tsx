import * as React from 'react';
import { connect } from 'react-redux';
import { State as OptionsState } from '../../options/reducers/';
import { State as TimerState } from '../../reducers/timer';
import { getCurrentDelta } from './timer';

interface State {
    toStart?: number;
    toEnd?: number;
    visible?: boolean;
    inserted?: Date;
    hardVisible?: boolean;
}

class Timer extends React.Component<State, {}> {
    private timerId;

    public componentDidMount() {
        this.timerId = setInterval(() => this.forceUpdate(), 1000);
    }

    public componentWillUnmount() {
        clearInterval(this.timerId);
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
            <div className={'row clock ' + ((visible || hardVisible) ? 'small' : 'big')}>
                <span className={'col'}>
                    <span className={'time-value'}>{(h < 10 ? '0' + h : '' + h)}</span>
                    <span className={'time-label'}>Hours/Hodin</span>
                </span>
                <span className={'col'}>
                    <span className={'time-value'}>{(m < 10 ? '0' + m : '' + m)}</span>
                    <span className={'time-label'}>Minutes/Minút</span>
                </span>
                <span className={'col'}>
                    <span className={'time-value'}>{(s < 10 ? '0' + s : '' + s)}</span>
                    <span className={'time-label'}>Seconds/Sekund</span>
                </span>
            </div>
        );
    }
}

interface Store {
    timer: TimerState;
    options: OptionsState;
}

const mapStateToProps = (state: Store): State => {
    return {
        hardVisible: state.options.hardVisible,
        inserted: state.timer.inserted,
        toEnd: state.timer.toEnd,
        toStart: state.timer.toStart,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(Timer);
