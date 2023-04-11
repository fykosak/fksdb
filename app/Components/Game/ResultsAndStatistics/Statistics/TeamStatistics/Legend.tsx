import { translator } from '@translator/translator';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setNewState } from '../../actions/stats';
import { State } from '../../reducers/stats';
import './legend.scss';

interface StateProps {
    onSetNewState(data: State): void;
}

class Legend extends React.Component<StateProps> {

    public render() {
        const availablePoints = [1, 2, 3, 5];
        const {onSetNewState} = this.props;
        const legend = availablePoints.map((points: number) => {
            let pointsLabel;
            switch (points) {
                case 1:
                    pointsLabel = translator.getText('bod');
                    break;
                case 2:
                case 3:
                    pointsLabel = translator.getText('body');
                    break;
                default:
                    pointsLabel = translator.getText('bodů');
            }
            return (<div key={points}
                         className="col-12 chart-legend-item"
                         onMouseEnter={() => {
                             onSetNewState({activePoints: +points})
                         }}
                         onMouseLeave={() => {
                             onSetNewState({activePoints: null})
                         }}>
                <i className="icon icon-circle" data-points={points}/>
                <strong>{points + ' ' + pointsLabel}</strong>
            </div>);
        });

        return (
            <div className="chart-legend chart-legend-game align-content-center col-lg-4 d-flex flex-wrap">
                {legend}
            </div>
        );
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): StateProps => {
    return {
        onSetNewState: data => dispatch(setNewState(data)),
    };
};

export default connect(null, mapDispatchToProps)(Legend);
