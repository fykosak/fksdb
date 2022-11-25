import * as React from 'react';
import { connect } from 'react-redux';
import { getCurrentDelta } from './Timer';
import './images.scss';
import { Store } from '../../../reducers/store';

interface StateProps {
    toStart: number;
    toEnd: number;
    inserted: Date;
}

class Images extends React.Component<StateProps> {
    private timerId: number;

    public componentDidMount() {
        this.timerId = window.setInterval(() => this.forceUpdate(), 1000);
    }

    public componentWillUnmount() {
        clearInterval(this.timerId);
    }

    public render() {
        const {inserted, toStart: rawToStart, toEnd: rawToEnd} = this.props;
        const {toStart, toEnd} = getCurrentDelta(rawToStart, rawToEnd, inserted);

        if (toStart === 0 || toEnd === 0) {
            return null;
        }
        return (
            <div className="presentation-images">
                {this.getLabel(toStart, toEnd)}
            </div>
        );
    }

    private getLabel(toStart: number, toEnd: number): string {
        if (toStart > 300 * 1000) {
            return 'Have not begun yet/Ješte nezačalo';
        }
        if (toStart > 0) {
            return 'Will soon begin/Brzy začne';
        }
        if (toStart > -120 * 1000) {
            return 'Start!';
        }
        if (toEnd > 0) {
            return null;
        }
        if (toEnd > -240 * 1000) {
            return 'Ended/Skončilo';
        }
        return 'Waiting for results/Čeká na výsledky';
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        inserted: state.timer.inserted,
        toEnd: state.timer.toEnd,
        toStart: state.timer.toStart,
    };
};

export default connect(mapStateToProps, null)(Images);
