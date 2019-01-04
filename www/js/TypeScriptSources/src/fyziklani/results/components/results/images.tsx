import * as React from 'react';
import { connect } from 'react-redux';
import { getCurrentDelta } from '../../../helpers/components/timer/timer';
import { IFyziklaniResultsStore } from '../../reducers';

interface IState {
    toStart?: number;
    toEnd?: number;
    inserted?: Date;
    visible?: boolean;
}

class Images extends React.Component<IState, {}> {
    private timerId = null;

    public componentDidMount() {
        this.timerId = setInterval(() => this.forceUpdate(), 1000);
    }

    public componentWillUnmount() {
        clearInterval(this.timerId);
    }

    public render() {
        const {inserted, toStart, toEnd} = this.props;
        const {currentToStart, currentToEnd} = getCurrentDelta({toStart, toEnd}, inserted);

        if (currentToStart === 0 || currentToEnd === 0) {
            return (<div/>);
        }
        let label = '';
        if (currentToStart > 300 * 1000) {
            label = 'Have not begun yet/Ješte nezačalo';
        } else if (currentToStart > 0) {
            label = 'Will soon begin/Brzo začne';
        } else if (currentToStart > -120 * 1000) {
            label = 'Start!';
        } else if (currentToEnd > 0) {
            label = null;
        } else if (currentToEnd > -240 * 1000) {
            label = 'Ended/Skončilo';
        } else {
            label = 'Waiting for results/Čeká na výsledky';
        }
        return (
            <div className="image-wp">
                {label}
            </div>
        );
    }
}

const mapStateToProps = (state: IFyziklaniResultsStore): IState => {
    return {
        inserted: state.timer.inserted,
        toEnd: state.timer.toEnd,
        toStart: state.timer.toStart,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(Images);
