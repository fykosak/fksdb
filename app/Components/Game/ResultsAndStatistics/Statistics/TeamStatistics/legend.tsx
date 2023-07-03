import * as React from 'react';
import { connect } from 'react-redux';
import { Action, Dispatch } from 'redux';
import { setNewState } from '../../actions/stats';
import { State } from '../../reducers/stats';
import { TranslatorContext } from '@translator/LangContext';
import LegendItem from 'FKSDB/Components/Charts/Core/LineChart/legend-item';

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
            return <LegendItem
                key={points}
                item={{
                    name: points + ' ' + pointsLabel,
                    color: 'var(--color-fof-points-' + points + ')',
                    display: {
                        points: true,
                    },
                }}/>;
        });

        return <div className="chart-legend row row-cols-lg-5">
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
