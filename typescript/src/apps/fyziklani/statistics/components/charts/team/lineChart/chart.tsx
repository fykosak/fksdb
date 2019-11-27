import {
    axisBottom,
    axisLeft,
} from 'd3-axis';
import {
    ScaleLinear,
    scaleLinear,
    ScaleTime,
    scaleTime,
} from 'd3-scale';
import { select } from 'd3-selection';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Submit,
    Submits,
    Task,
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import {
    getLinePath,
    PointData,
} from '../../../../middleware/charts/lines';
import { Store as StatisticsStore } from '../../../../reducers';

interface StateProps {
    submits: Submits;
    tasks: Task[];
    gameStart: Date;
    gameEnd: Date;
    activePoints: number;
}

interface OwnProps {
    teamId: number;
}

export interface ExtendedSubmit extends Submit {
    totalPoints: number;
    currentTask: Task;
}

class PointsInTime extends React.Component<StateProps & OwnProps, {}> {

    private xAxis: SVGElement;
    private yAxis: SVGElement;

    private xScale: ScaleTime<number, number>;
    private yScale: ScaleLinear<number, number>;

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
            gameEnd,
            gameStart,
        } = this.props;

        const teamSubmits: ExtendedSubmit[] = [];

        let maxPoints = 0;

        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: Submit = submits[index];
                const {teamId: submitTeamId, points} = submit;
                if (teamId === submitTeamId) {
                    const currentTask = tasks.filter((task) => {
                        return submit.taskId === task.taskId;
                    })[0];
                    if (points !== null && points !== 0) {
                        maxPoints += +points;
                        teamSubmits.push({
                            ...submit,
                            currentTask,
                            totalPoints: maxPoints,
                        });
                    }
                }
            }
        }

        this.xScale = scaleTime<number, number>().domain([gameStart, gameEnd]).range([30, 580]);
        this.yScale = scaleLinear<number, number>().domain([0, maxPoints]).range([370, 20]);
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
                        {submit.currentTask.label} - {(new Date(submit.created)).toLocaleTimeString()}
                    </title>
                </circle>
            );
        });
        const pointsData: PointData[] = [
            {
                created: gameStart.toString(),
                totalPoints: 0,
            },
            ...teamSubmits,
            {
                created: gameEnd.toString(),
                totalPoints: maxPoints,
            },
        ];
        const linePath = getLinePath({xScale: this.xScale, yScale: this.yScale}, pointsData);

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

    private getAxis(): void {
        const xAxis = axisBottom<Date>(this.xScale);
        select(this.xAxis).call(xAxis);

        const yAxis = axisLeft<number>(this.yScale);
        select(this.yAxis).call(yAxis);
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
    return {
        activePoints: state.statistics.activePoints,
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        submits: state.data.submits,
        tasks: state.data.tasks,
    };
};

export default connect(mapStateToProps, null)(PointsInTime);
