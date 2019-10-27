import { arc } from 'd3-shape';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Submit,
    Submits,
    Team,
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { getPieData } from '../../../../middleware/charts/pie';
import { Store as StatisticsStore } from '../../../../reducers';

interface State {
    teams?: Team[];
    submits?: Submits;
    activePoints?: number;
}

interface Props {
    teamId: number;
}

interface Item {
    points: number;
    count: number;
}

class Chart extends React.Component<State & Props, {}> {

    public render() {
        const {submits, teamId, activePoints} = this.props;

        const pointsCategories: { [key: number]: Item } = {
            1: {points: 1, count: 0},
            2: {points: 2, count: 0},
            3: {points: 3, count: 0},
            5: {points: 5, count: 0},
        };

        let totalSubmits = 0;
        let maxPoints = 0;
        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: Submit = submits[index];
                const {teamId: submitTeamId, points} = submit;
                if (teamId === submitTeamId) {

                    if (points !== null && points !== 0) {
                        if (pointsCategories.hasOwnProperty(points)) {
                            totalSubmits++;
                            pointsCategories[points].count++;
                            maxPoints += +points;
                        }
                    }
                }
            }
        }
        const arcEl = arc().innerRadius(0).outerRadius(150);
        const filteredData = [];
        for (const points in pointsCategories) {
            if (pointsCategories.hasOwnProperty(points)) {
                const item = pointsCategories[points];
                if (item.count !== 0) {
                    filteredData.push(item);
                }
            }
        }

        const pie = getPieData(filteredData);

        const paths = pie.map((item: any, index: number) => {
            return (<path
                    stroke="white"
                    strokeWidth="5px"
                    d={arcEl(item)}
                    key={index}
                    fill={getColorByPoints(item.data.points)}
                    opacity={(activePoints && (activePoints !== item.data.points)) ? '0.5' : '1'}
                />
            );
        });

        const labels = pie.map((item: any, index: number) => {
            return (
                <g key={index}>
                    <text textAnchor="middle" transform={`translate(${arcEl.centroid(item).toString()})`}>
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

const mapStateToProps = (state: StatisticsStore): State => {
    return {
        activePoints: state.statistics.activePoints,
        submits: state.data.submits,
        teams: state.data.teams,
    };
};

export default connect(mapStateToProps, null)(Chart);
