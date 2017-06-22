import * as React from 'react';
import {connect} from 'react-redux';

import {
    getColorByPoints,
} from '../../../../helpers/pie/index';
import {
    setActivePoints,
    setDeActivePoints,
} from '../../../../actions/stats';

interface IProps {
    onActivePoints?: Function;
    onDeActivePoints?: Function;
}

class Legend extends React.Component<IProps, void> {

    render() {
        const availablePoints = [1, 2, 3, 5];
        const {onActivePoints, onDeActivePoints} = this.props;
        const legend = availablePoints.map((points: number) => {
            return (<div className="w-100 legend-item"
                         onMouseEnter={() => {
                             onActivePoints(points)
                         }}
                         onMouseLeave={() => {
                             onDeActivePoints()
                         }}>
                <i className="icon" style={{'background-color': getColorByPoints(points)}}/>
                <strong>{points} points</strong>
            </div>);
        });

        return (
            <div className="align-content-center col-lg-4 d-flex flex-wrap">
                {legend}
            </div>
        );
    }
}

const mapDispatchToProps = (dispatch, ownProps) => {
    return {
        ...ownProps,
        onActivePoints: (points) => dispatch(setActivePoints(+points)),
        onDeActivePoints: () => dispatch(setDeActivePoints()),
    };
};

export default connect(null, mapDispatchToProps)(Legend);
