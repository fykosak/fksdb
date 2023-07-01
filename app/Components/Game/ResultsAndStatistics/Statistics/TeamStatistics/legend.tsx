import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { setNewState } from '../../actions/stats';
import { State } from '../../reducers/stats';
import './legend.scss';
import { TranslatorContext } from '@translator/LangContext';

interface StateProps {
    onSetNewState(data: State): void;
}

class Legend extends React.Component<StateProps, never> {
    static contextType = TranslatorContext;

    public render() {
        const translator = this.context;
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
            return <div key={points}
                        className="col chart-legend-item"
                        onMouseEnter={() => {
                            onSetNewState({activePoints: +points})
                        }}
                        onMouseLeave={() => {
                            onSetNewState({activePoints: null})
                        }}>
                <i className="icon icon-circle"
                   data-points={points}
                   style={{'--item-color': 'var(--color-fof-points-' + points + ')'} as React.CSSProperties}
                />
                <strong>{points + ' ' + pointsLabel}</strong>
            </div>;
        });

        return <div className="chart-legend chart-legend-game align-content-center d-flex flex-wrap">
            {legend}
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): StateProps => {
    return {
        onSetNewState: data => dispatch(setNewState(data)),
    };
};

export default connect(null, mapDispatchToProps)(Legend);
