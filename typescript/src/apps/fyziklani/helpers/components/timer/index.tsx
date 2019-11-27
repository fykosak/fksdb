import * as React from 'react';
import { connect } from 'react-redux';
import { State as OptionsState } from '../../options/reducers/';
import { State as TimerState } from '../../reducers/timer';
import { getCurrentDelta } from './timer';

interface OwnProps {
    mode: "big" | "small";
}

interface StateProps {
    toStart: number;
    toEnd: number;
    inserted: Date;
}

class Timer extends React.Component<StateProps & OwnProps, {}> {
    private timerId;

    public componentDidMount() {
        this.timerId = setInterval(() => this.forceUpdate(), 1000);
    }

    public componentWillUnmount() {
        clearInterval(this.timerId);
    }

    public render() {
        const {inserted, toStart: rawToStart, toEnd: rawToEnd, mode} = this.props;
        const {toStart, toEnd} = getCurrentDelta(rawToStart, rawToEnd, inserted);
        let timeStamp = 0;
        if (toStart > 0) {
            timeStamp = toStart;
        } else if (toEnd > 0) {
            timeStamp = toEnd;
        } else {
            return null;
        }
        const date = new Date(timeStamp);
        const h = date.getUTCHours();
        const m = date.getUTCMinutes();
        const s = date.getUTCSeconds();
        return (
            <div className={'row clock clock-' + mode}>
                <span className={'col'}>
                    <span className={'time-value'}>{(h < 10 ? '0' + h : '' + h)}</span>
                    <span className={'time-label'}>Hours/Hodin</span>
                </span>
                <span className={'col'}>
                    <span className={'time-value'}>{(m < 10 ? '0' + m : '' + m)}</span>
                    <span className={'time-label'}>Minutes/Minut</span>
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

const mapStateToProps = (state: Store): StateProps => {
    return {
        inserted: state.timer.inserted,
        toEnd: state.timer.toEnd,
        toStart: state.timer.toStart,
    };
};

export default connect(mapStateToProps, null)(Timer);
