import * as React from 'react';
import { connect } from 'react-redux';
import { getCurrentDelta } from '../../../helpers/components/timer/timer';
import { FyziklaniResultsStore } from '../../reducers';

interface State {
    toStart?: number;
    toEnd?: number;
    inserted?: Date;
    visible?: boolean;
}

class Images extends React.Component<State, {}> {
    private timerId = null;

    public componentDidMount() {
        this.timerId = setInterval(() => this.forceUpdate(), 1000);
    }

    public componentWillUnmount() {
        clearInterval(this.timerId);
    }

    public render() {
        const {inserted, toStart: rawToStart, toEnd: rawToEnd} = this.props;
        const {toStart, toEnd} = getCurrentDelta(rawToStart, rawToEnd, inserted);

        if (toStart === 0 || toEnd === 0) {
            return (<div/>);
        }
        let label = '';
        if (toStart > 300 * 1000) {
            label = 'Have not begun yet/Ješte nezačalo';
        } else if (toStart > 0) {
            label = 'Will soon begin/Brzo začne';
        } else if (toStart > -120 * 1000) {
            label = 'Start!';
        } else if (toEnd > 0) {
            label = null;
        } else if (toEnd > -240 * 1000) {
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

const mapStateToProps = (state: FyziklaniResultsStore): State => {
    return {
        inserted: state.timer.inserted,
        toEnd: state.timer.toEnd,
        toStart: state.timer.toStart,
        visible: state.timer.visible,
    };
};

export default connect(mapStateToProps, null)(Images);
