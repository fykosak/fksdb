import * as React from 'react';
import { connect } from 'react-redux';
import { IFyziklaniOptionsState } from '../../options/reducers/';
import { IFyziklaniTimerState } from '../../reducers/timer';
import { getCurrentDelta } from './timer';

interface IState {
    toStart?: number;
    toEnd?: number;
    visible?: boolean;
    inserted?: Date;
    hardVisible?: boolean;
}

class Timer extends React.Component<IState, {}> {
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
            <div className={'clock ' + ((visible || hardVisible) ? '' : 'big')}>
                {
                    (h < 10 ? '0' + h : '' + h)
                    + ':' +
                    (m < 10 ? '0' + m : '' + m)
                    + ':' +
                    (s < 10 ? '0' + s : '' + s)
                }
            </div>
        );
    }
}

interface IStore {
    timer: IFyziklaniTimerState;
    options: IFyziklaniOptionsState;
}

const mapStateToProps = (state: IStore): IState => {
    return {
        hardVisible: state.options.hardVisible,
        inserted: state.timer.inserted,
        toEnd: state.timer.toEnd,
        toStart: state.timer.toStart,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(Timer);
