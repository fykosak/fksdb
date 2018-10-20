import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../../../i18n/i18n';
import { setActivePoints } from '../../../actions/';
import { getColorByPoints } from '../../../middleware/charts/colors';
import { IFyziklaniStatisticsStore } from '../../../reducers';

interface IState {
    onActivePoints?: (points: number) => void;
}

interface IProps {
    inline: boolean;
}

class Legend extends React.Component<IProps & IState, {}> {

    public render() {
        const availablePoints = [1, 2, 3, 5];
        const {onActivePoints, inline} = this.props;
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
                         className={inline ? 'legend-item col-3' : 'w-100 legend-item'}
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
            <div className={inline ? 'row col-12' : 'align-content-center col-lg-4 d-flex flex-wrap'}>
                {legend}
            </div>
        );
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniStatisticsStore>): IState => {
    return {
        onActivePoints: (points) => dispatch(setActivePoints(+points)),
    };
};

export default connect(null, mapDispatchToProps)(Legend);
