import * as d3 from 'd3';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    ISubmit,
    ISubmits,
    ITask,
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { IFyziklaniStatisticsStore } from '../../../../reducers';

interface IState {
    activePoints?: number;
    submits?: ISubmits;
    tasks?: ITask[];
    gameStart?: Date;
    gameEnd?: Date;
}

interface IExtendedTask extends ITask {
    from: Date;
}

interface IProps {
    teamId: number;
}

class TimeLine extends React.Component<IState & IProps, {}> {

    private xAxis: any;
    private ySize: number;

    private xScale: d3.ScaleTime<number, number>;
    private yScale: d3.ScaleLinear<number, number>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const {teamId, submits, tasks, gameStart, gameEnd, activePoints} = this.props;

        const {activeTasks, teamSubmits} = reconstructTeamGame(submits, tasks, 7, gameStart, teamId);

        this.ySize = (activeTasks.length * 12) + 20;

        this.xScale = d3.scaleTime<number, number>().domain([gameStart, gameEnd]).range([30, 580]);
        this.yScale = d3.scaleLinear<number, number>().domain([0, activeTasks.length]).range([20, this.ySize - 30]);

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
                        points={`${fromCoordinates},${yCoordinates} ${toCoordinates},${yCoordinates}`}
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
                <svg viewBox={`0 0 600 ${this.ySize}`} className="chart time-line">
                    <g transform={`translate(0,${this.ySize - 30})`} className="x axis"
                       ref={(xAxis) => this.xAxis = xAxis}/>
                    {dots}
                </svg>
            </div>
        );
    }

    private getAxis() {
        const xAxis = d3.axisBottom(this.xScale).tickSizeInner(-this.ySize).tickArguments([d3.timeMinute.every(30)]);
        d3.select(this.xAxis).call(xAxis);
    }
}

const mapStateToProps = (state: IFyziklaniStatisticsStore): IState => {
    return {
        activePoints: state.statistics.activePoints,
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        submits: state.data.submits,
        tasks: state.data.tasks,
    };
};

export default connect(mapStateToProps, null)(TimeLine);

const reconstructTeamGame = (submits: ISubmits, tasks: ITask[], taskOnBoard: number, gameStart: Date, teamId: number):
    { activeTasks: IExtendedTask[]; teamSubmits: ISubmit[] } => {
    const taskBuffer = [...(tasks.slice(taskOnBoard))];
    const teamSubmits = [];
    const activeTasks: IExtendedTask[] = [];

    for (let i = 0; i < taskOnBoard; i++) {
        activeTasks.push({
            ...tasks[i],
            from: gameStart,
        });
    }
    for (const index in submits) {
        if (submits.hasOwnProperty(index)) {
            const submit: ISubmit = submits[index];
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
