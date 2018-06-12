import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import Lang from '../../../../../lang/components/lang';
import {
    setActivePoints,
    setDeActivePoints,
} from '../../../../actions/stats';
import { getColorByPoints } from '../../../../helpers/pie/';
import { IStore } from '../../../../reducers/';

interface IState {
    onActivePoints?: (points: number) => void;
    onDeActivePoints?: () => void;
}

interface IProps {
    inline: boolean;
}

class Legend extends React.Component<IProps & IState, {}> {

    public render() {
        const availablePoints = [1, 2, 3, 5];
        const {onActivePoints, onDeActivePoints, inline} = this.props;
        const legend = availablePoints.map((points: number) => {
            let pointsLabel = null;
            switch (points) {
                case 1:
                    pointsLabel = <Lang text={'bod'}/>;
                    break;
                case 2:
                case 3:
                    pointsLabel = <Lang text={'body'}/>;
                    break;
                default:
                    pointsLabel = <Lang text={'bodů'}/>;
            }
            return (<div key={points}
                         className={inline ? 'legend-item col-3' : 'w-100 legend-item'}
                         onMouseEnter={() => {
                             onActivePoints(points);
                         }}
                         onMouseLeave={() => {
                             onDeActivePoints();
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

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onActivePoints: (points) => dispatch(setActivePoints(+points)),
        onDeActivePoints: () => dispatch(setDeActivePoints()),
    };
};

export default connect(null, mapDispatchToProps)(Legend);
