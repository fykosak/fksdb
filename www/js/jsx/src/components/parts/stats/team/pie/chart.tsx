import * as React from 'react';
import {connect} from 'react-redux';
import * as d3 from 'd3';
import {
    ISubmit,
    ITeam,
} from '../../../../../helpers/interfaces';
import {
    getPieData,
    getColorByPoints,
} from '../../../../../helpers/pie/index';

interface IProps {
    teams?: Array<ITeam>;
    submits?: any;
    teamID?: number;
    activePoints?: number;
}

class Chart extends React.Component<IProps, void> {

    render() {
        const {submits, teamID, activePoints} = this.props;

        if (!teamID) {
            return (<div/>);
        }
        const teamSubmits = [];
        const pointsCategories = [
            {points: 0, count: 0},
            {points: 1, count: 0},
            {points: 2, count: 0},
            {points: 3, count: 0},
            {points: 4, count: 0},
            {points: 5, count: 0},
        ];

        let totalSubmits = 0;
        let maxPoints = 0;
        for (let index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: ISubmit = submits[index];
                const {team_id, points} = submit;
                if (teamID === team_id) {
                    totalSubmits++;
                    pointsCategories[points].count++;
                    maxPoints += +points;
                    teamSubmits.push({
                        ...submit,
                        totalPoints: maxPoints,
                    });
                }
            }
        }
        const arc = d3.arc().innerRadius(0).outerRadius(150);
        const pie = getPieData(pointsCategories.filter((item) => item.count !== 0));

        const paths = pie.map((item: any) => {
            return (<path
                    stroke="white"
                    strokeWidth="5px"
                    d={arc(item)}
                    fill={getColorByPoints(item.data.points)}
                    opacity={(activePoints && (activePoints !== item.data.points)) ? '0.5' : '1'}
                />
            );
        });

        const labels = pie.map((item: any) => {
            return (
                <g>
                    <text textAnchor="middle" transform={'translate(' + arc.centroid(item).toString() + ')'}>
                        {Math.floor(item.data.count * 100 / totalSubmits)}%
                    </text>
                </g>
            );
        });

        const pieChart = (<svg viewBox="0 0 400 400">
            <g transform="translate(200,200)">
                {paths}
                {labels}
            </g>
        </svg>);

        return (
            <div className="col-lg-8">
                {pieChart}
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps: IProps): IProps => {
    return {
        ...ownProps,
        teams: state.results.teams,
        submits: state.results.submits,
        teamID: state.stats.teamID,
        activePoints: state.stats.activePoints,
    };
};

export default connect(mapStateToProps, null)(Chart);
