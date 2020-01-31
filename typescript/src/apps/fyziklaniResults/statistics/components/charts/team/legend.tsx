import { lang } from '@i18n/i18n';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Action,
    Dispatch,
} from 'redux';
import { setActivePoints } from '../../../actions';
import { getColorByPoints } from '../../../middleware/charts/colors';

interface StateProps {
    onActivePoints: (points: number) => void;
}

class Legend extends React.Component<StateProps, {}> {

    public render() {
        const availablePoints = [1, 2, 3, 5];
        const {onActivePoints} = this.props;
        const legend = availablePoints.map((points: number) => {
            let pointsLabel = '';
            switch (points) {
                case 1:
                    pointsLabel = lang.getText('bod');
                    break;
                case 2:
                case 3:
                    pointsLabel = lang.getText('body');
                    break;
                default:
                    pointsLabel = lang.getText('bodů');
            }
            return (<div key={points}
                         className="col-12 legend-item"
                         onMouseEnter={() => {
                             onActivePoints(points);
                         }}
                         onMouseLeave={() => {
                             onActivePoints(null);
                         }}>
                <i className="icon" style={{backgroundColor: getColorByPoints(points)}}/>
                <strong>{points + ' ' + pointsLabel}</strong>
            </div>);
        });

        return (
            <div className={'align-content-center col-lg-4 d-flex flex-wrap'}>
                {legend}
            </div>
        );
    }
}

const mapDispatchToProps = (dispatch: Dispatch<Action<string>>): StateProps => {
    return {
        onActivePoints: (points) => dispatch(setActivePoints(+points)),
    };
};

export default connect(null, mapDispatchToProps)(Legend);
