import * as d3 from 'd3';
import * as React from 'react';

import {
    connect,
    Dispatch,
} from 'react-redux';

import {
    ISubmit,
    ISubmits,
    ITask,
} from '../../../../../../shared/interfaces';
import {
    setActivePoints,
    setDeActivePoints,
} from '../../../../../actions/stats';
import { getLinePath } from '../../../../../helpers/lines';
import { getColorByPoints } from '../../../../../helpers/pie/index';
import { IStore } from '../../../../../reducers/index';

interface IState {
    submits?: ISubmits;
    tasks?: ITask[];
    teamId?: number;
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

    private xScale: d3.ScaleTime<any, any>;
    private yScale: d3.ScaleLinear<any, any>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const {
            teamId,
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
                const { teamId: submitTeamId, points } = submit;
                if (teamId === submitTeamId) {
                    const currentTask = tasks.filter((task) => {
                        return submit.taskId === task.taskId;
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
        const dots = teamSubmits.map((submit, index) => {
            return (
                <circle
                    key={index}
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
        teamId: state.stats.teamId,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IStore>): IState => {
    return {
        onActivePoints: (points) => dispatch(setActivePoints(+points)),
        onDeActivePoints: () => dispatch(setDeActivePoints()),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(PointsInTime);
