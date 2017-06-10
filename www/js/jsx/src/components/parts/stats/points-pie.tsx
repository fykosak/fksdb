import * as React from 'react';
import {connect} from 'react-redux';
import * as d3 from 'd3';
import {
    ISubmit,
    ITeam,
} from '../../../helpers/interfaces';
import {
    getPieData,
    getColorByPoints
} from '../../../helpers/pie/index';

interface IProps {
    teams: Array<ITeam>;
    submits: any;
    teamID: number;
}
interface IState {
    activePoints: number;
}

class PointsPie extends React.Component<IProps, IState> {
    public constructor() {
        super();
        this.state = {activePoints: null};
    }

    render() {
        const {submits, teamID} = this.props;
        const {activePoints} = this.state;

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
            return (
                <g>
                    <path
                        stroke="white"
                        strokeWidth="5px"
                        d={arc(item)}
                        fill={getColorByPoints(item.data.points)}
                        opacity={(activePoints && (activePoints !== item.data.points)) ? '0.5' : '1'}/>
                </g>
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
        const legend = pointsCategories.filter((item) => item.count !== 0).map((item) => {
            return (<div className="w-100"
                         onMouseEnter={() => {
                             this.setState({activePoints: item.points})
                         }}
                         onMouseLeave={() => {
                             this.setState({activePoints: null})
                         }}>
                <i style={{'background-color': getColorByPoints(item.points), display: 'inline-block', height: '1rem', width: '1rem'}}/>
                <span> <strong>{item.points} points</strong>- {item.count}
                    ({Math.floor(item.count * 100 / totalSubmits)}%)</span>
            </div>);
        });

        return (<div>
            <h3>Úspešnosť odovzdávania úloh</h3>
            <div className="row">
                <div className="col-lg-8">
                    {pieChart}
                </div>
                <div className="align-content-center col-lg-4 d-flex flex-wrap">
                    {legend}
                </div>
            </div>
        </div>);
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        teams: state.results.teams,
        submits: state.results.submits,
    }
};

export default connect(mapStateToProps, null)(PointsPie);
