import AbstractChart from '@shared/components/chart';
import { axisBottom } from 'd3-axis';
import {
    ScaleLinear,
    scaleLinear,
    ScaleTime,
    scaleTime,
} from 'd3-scale';
import { select } from 'd3-selection';
import { timeMinute } from 'd3-time';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Submit,
    Submits,
    Task,
} from '../../../../../../fyziklani/helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { Store } from '../../../../reducers';

interface StateProps {
    activePoints: number;
    submits: Submits;
    tasks: Task[];
    gameStart: Date;
    gameEnd: Date;
    tasksOnBoard: number;
}

interface ExtendedTask extends Task {
    from: Date;
}

interface OwnProps {
    teamId: number;
}

class TimeLine extends AbstractChart<StateProps & OwnProps, {}> {

    private xAxis: SVGElement;
    private ySize: number;

    private xScale: ScaleTime<number, number>;
    private yScale: ScaleLinear<number, number>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const {teamId, submits, tasks, gameStart, gameEnd, activePoints, tasksOnBoard} = this.props;

        const {activeTasks, teamSubmits} = reconstructTeamGame(submits, tasks, tasksOnBoard, gameStart, teamId);

        this.ySize = (activeTasks.length * 12) + 20;

        this.xScale = scaleTime<number, number>().domain([gameStart, gameEnd]).range(this.getInnerXSize());
        this.yScale = scaleLinear<number, number>().domain([0, activeTasks.length]).range([20, this.ySize - 30]);

        const dots = activeTasks.map((task, index: number) => {
            const {taskId, from} = task;
            const submit = teamSubmits.filter((fSubmit) => {
                return fSubmit.taskId === taskId;
            })[0];
            const to = submit ? new Date(submit.created) : gameEnd;

            const fromCoordinates = this.xScale(from);
            const toCoordinates = this.xScale(to);
            const yCoordinates = this.yScale(index);

            const color = getColorByPoints(submit ? submit.points : null);
            let active = true;
            if (activePoints) {
                active = false;
                if (submit) {
                    active = activePoints === submit.points;
                }
            }
            return (
                <g style={{opacity: (active) ? 1 : 0.1}} key={index}>
                    <polyline
                        points={fromCoordinates + ',' + yCoordinates + ' ' + toCoordinates + ',' + yCoordinates}
                        strokeWidth="2"
                        strokeLinecap="round"
                        stroke={color}
                    />
                    <text
                        x={(fromCoordinates + toCoordinates) / 2}
                        y={yCoordinates - 1}
                        fontSize="10"
                        textAnchor="middle"
                    >
                        {task.label}
                    </text>
                </g>
            );
        });

        return (
            <div className="col-lg-12">
                <svg viewBox={'0 0 ' + this.size.width + ' ' + this.ySize} className="chart time-line">
                    <g transform={'translate(0,' + (this.ySize - this.margin.bottom) + ')'} className="x axis"
                       ref={(xAxis) => this.xAxis = xAxis}/>
                    {dots}
                </svg>
            </div>
        );
    }

    private getAxis() {
        const xAxis = axisBottom<Date>(this.xScale).tickSizeInner(-this.ySize).tickArguments([timeMinute.every(30)]);
        select(this.xAxis).call(xAxis);
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        activePoints: state.statistics.activePoints,
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        submits: state.data.submits,
        tasks: state.data.tasks,
        tasksOnBoard: state.data.tasksOnBoard,
    };
};

export default connect(mapStateToProps, null)(TimeLine);

const reconstructTeamGame = (submits: Submits, tasks: Task[], tasksOnBoard: number, gameStart: Date, teamId: number):
    { activeTasks: ExtendedTask[]; teamSubmits: Submit[] } => {
    const taskBuffer = [...(tasks.slice(tasksOnBoard))];
    const teamSubmits = [];
    const activeTasks: ExtendedTask[] = [];

    for (let i = 0; i < tasksOnBoard; i++) {
        activeTasks.push({
            ...tasks[i],
            from: gameStart,
        });
    }
    for (const index in submits) {
        if (submits.hasOwnProperty(index)) {
            const submit: Submit = submits[index];
            const {teamId: submitTeamId, created} = submit;
            if (teamId === submitTeamId) {
                if (submit.points !== null && submit.points !== 0) {
                    teamSubmits.push(submit);
                    const task = taskBuffer.shift();
                    activeTasks.push({
                        ...task,
                        from: new Date(created),
                    });
                }
            }
        }
    }
    taskBuffer.sort((a, b) => {
        return a.taskId - b.taskId;
    });

    return {activeTasks, teamSubmits};
};
