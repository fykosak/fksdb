import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import {
    setActivePoints,
    setDeActivePoints,
} from '../../../../actions/stats';
import { getColorByPoints } from '../../../../helpers/pie/index';

import { IStore } from '../../../../reducers/index';

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
        const { onActivePoints, onDeActivePoints, inline } = this.props;
        const legend = availablePoints.map((points: number) => {
            return (<div className={inline ? 'legend-item col-3' : 'w-100 legend-item'}
                         onMouseEnter={() => {
                             onActivePoints(points);
                         }}
                         onMouseLeave={() => {
                             onDeActivePoints();
                         }}>
                <i className="icon" style={{ 'background-color': getColorByPoints(points) }}/>
                <strong>{points} points</strong>
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
