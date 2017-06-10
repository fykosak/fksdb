import * as React from 'react';
import {connect} from 'react-redux';
import * as d3 from 'd3';

import {ISubmit} from '../../../helpers/interfaces';
import {
    ScaleLinear,
    ScaleTime,
} from 'd3-scale';

import {getColorByPoints} from '../../../helpers/pie/index';

interface IProps {
    submits: any;
    teamID: number;
}
interface IState {

    activePoints: number;
}

class PointsInTime extends React.Component<IProps, IState> {

    private xAxis: any;
    private yAxis: any;

    private xScale: ScaleTime<any, any>;
    private yScale: ScaleLinear<any, any>;

    public constructor() {
        super();
        this.state = {activePoints: null};
    }

    componentDidMount() {
        this.getAxis();
    }

    componentDidUpdate() {
        this.getAxis();
    }

    private getAxis() {
        const xAxis = d3.axisBottom(this.xScale);
        d3.select(this.xAxis).call(xAxis);

        const yAxis = d3.axisLeft(this.yScale);
        d3.select(this.yAxis).call(yAxis);
    }

    render() {
        const {teamID, submits} = this.props;
        const {activePoints} = this.state;

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

        const [minDate, maxDate] = d3.extent(teamSubmits, (element) => {
            return new Date(element.created);
        });

        this.xScale = d3.scaleTime().domain([minDate, maxDate]).range([30, 580]);
        this.yScale = d3.scaleLinear().domain([0, maxPoints]).range([370, 20]);
        const dots = teamSubmits.map((submit) => {
            return (
                <circle
                    opacity={(activePoints && (activePoints !== submit.points)) ? '0' : '1'}
                    r="7.5"
                    fill={getColorByPoints(submit.points)}
                    stroke="white"
                    strokeWidth="2.5"
                    cy={this.yScale(submit.totalPoints)}
                    cx={this.xScale(new Date(submit.created))}
                />
            );
        });

        const linePath = d3.line()
            .x((element: any) => {
                return this.xScale(new Date(element.created));
            })
            .y((element: any) => {
                return this.yScale(element.totalPoints);
            })(teamSubmits);
        const line = (<path d={linePath} stroke="#1175da" strokeWidth="5px" fill="none"/>);

        const svg = (<svg viewBox="0 0 600 400">
            <g>
                <g transform="translate(0,370)" className="x axis" ref={(xAxis) => this.xAxis = xAxis}/>
                <g transform="translate(30,0)" className="x axis" ref={(yAxis) => this.yAxis = yAxis}/>
                {line}
                {dots}
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
                <strong>{item.points} points</strong>
            </div>);
        });
        return (<div>
                <h3>Časový vývoj počtu bodov</h3>
                <div className="row">

                    <div className="col-lg-8">{svg}</div>
                    <div className="align-content-center col-lg-4 d-flex flex-wrap">
                        {legend}
                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps) => {
    return {
        ...ownProps,
        submits: state.results.submits,
    };
};

export default connect(mapStateToProps, null)(PointsInTime);
