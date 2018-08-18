import * as d3 from 'd3';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    ISubmit,
    ISubmits,
    ITeam,
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { getPieData } from '../../../../middleware/charts/pie';
import { IFyziklaniStatisticsStore } from '../../../../reducers';

interface IState {
    teams?: ITeam[];
    submits?: ISubmits;
    teamId?: number;
    activePoints?: number;
}

class Chart extends React.Component<IState, {}> {

    public render() {
        const {submits, teamId, activePoints} = this.props;

        if (!teamId) {
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
        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: ISubmit = submits[index];
                const {teamId: submitTeamId, points} = submit;
                if (teamId === submitTeamId) {
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

        const paths = pie.map((item: any, index: number) => {
            return (<path
                    stroke="white"
                    strokeWidth="5px"
                    d={arc(item)}
                    key={index}
                    fill={getColorByPoints(item.data.points)}
                    opacity={(activePoints && (activePoints !== item.data.points)) ? '0.5' : '1'}
                />
            );
        });

        const labels = pie.map((item: any, index: number) => {
            return (
                <g key={index}>
                    <text textAnchor="middle" transform={`translate(${arc.centroid(item).toString()})`}>
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

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        activePoints: state.statistics.activePoints,
        submits: state.data.submits,
        teamId: state.statistics.teamId,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Chart);
