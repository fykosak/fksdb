import * as d3 from 'd3';
import * as React from 'react';

import {
    ScaleLinear,
    ScaleTime,
} from 'd3-scale';
import {
    connect,
    Dispatch,
} from 'react-redux';

import {
    setActivePoints,
    setDeActivePoints,
} from '../../../../../actions/stats';
import {
    ISubmit,
    ISubmits,
    ITask,
} from '../../../../../helpers/interfaces';
import { getLinePath } from '../../../../../helpers/lines';
import { getColorByPoints } from '../../../../../helpers/pie/index';
import { IStore } from '../../../../../reducers/index';

interface IState {
    submits?: ISubmits;
    tasks?: ITask[];
    teamID?: number;
    gameStart?: Date;
    gameEnd?: Date;
    onDeActivePoints?: () => void;
    onActivePoints?: (points: number) => void;
    activePoints?: number;
}

interface IExtendedSubmit extends ISubmit {
    totalPoints: number;
    currentTask: ITask;
}

class PointsInTime extends React.Component<IState, {}> {

    private xAxis: any;
    private yAxis: any;

    private xScale: ScaleTime<any, any>;
    private yScale: ScaleLinear<any, any>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const {
            teamID,
            submits,
            tasks,
            activePoints,
        } = this.props;

        const teamSubmits: IExtendedSubmit[] = [];
        const pointsCategories = [
            { points: 0, count: 0 },
            { points: 1, count: 0 },
            { points: 2, count: 0 },
            { points: 3, count: 0 },
            { points: 4, count: 0 },
            { points: 5, count: 0 },
        ];

        let totalSubmits = 0;
        let maxPoints = 0;

        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: ISubmit = submits[index];
                const { team_id, points } = submit;
                if (teamID === team_id) {
                    const currentTask = tasks.filter((task) => {
                        return submit.task_id === task.task_id;
                    })[0];
                    totalSubmits++;
                    pointsCategories[points].count++;
                    maxPoints += +points;
                    teamSubmits.push({
                        ...submit,
                        currentTask,
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
                    cy={this.yScale(submit.totalPoints)}
                    cx={this.xScale(new Date(submit.created))}
                >
                    <title>
                        {`${submit.currentTask.label} - ${(new Date(submit.created)).toLocaleTimeString()}`}
                    </title>
                </circle>
            );
        });
        const linePath = getLinePath({ xScale: this.xScale, yScale: this.yScale }, teamSubmits);

        return (
            <div className="col-lg-8">
                <svg viewBox="0 0 600 400" className="chart points-in-time">
                    <g>
                        <g transform="translate(0,370)" className="x axis" ref={(xAxis) => this.xAxis = xAxis}/>
                        <g transform="translate(30,0)" className="x axis" ref={(yAxis) => this.yAxis = yAxis}/>
                        <path d={linePath} className="line"/>
                        {dots}
                    </g>
                </svg>
            </div>
        );
    }

    private getAxis() {
        const xAxis = d3.axisBottom(this.xScale);
        d3.select(this.xAxis).call(xAxis);

        const yAxis = d3.axisLeft(this.yScale);
        d3.select(this.yAxis).call(yAxis);
    }
}

const mapStateToProps = (state: IStore): IState => {
    return {
        activePoints: state.stats.activePoints,
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        submits: state.results.submits,
        tasks: state.results.tasks,
        teamID: state.stats.teamID,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onActivePoints: (points) => dispatch(setActivePoints(+points)),
        onDeActivePoints: () => dispatch(setDeActivePoints()),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(PointsInTime);
