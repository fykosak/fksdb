import * as d3 from 'd3';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    ISubmit,
    ISubmits,
    ITask,
} from '../../../../../../shared/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { IFyziklaniStatisticsStore } from '../../../../reducers';

interface IState {
    activePoints?: number;
    submits?: ISubmits;
    tasks?: ITask[];
    teamId?: number;
    gameStart?: Date;
    gameEnd?: Date;
}

class TimeLine extends React.Component<IState, {}> {

    private xAxis: any;
    private ySize: number;

    private xScale: d3.ScaleTime<any, any>;
    private yScale: d3.ScaleLinear<any, any>;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const { teamId, submits, tasks, gameStart, gameEnd, activePoints } = this.props;
        const taskOnBoard = 7;
        const taskBuffer = [...(tasks.slice(taskOnBoard))];

        const activeTasks: Array<ITask & { from: Date }> = [];

        for (let i = 0; i < taskOnBoard; i++) {
            activeTasks.push({
                ...tasks[i],
                from: gameStart,
            });
        }
        const teamSubmits = [];
        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: ISubmit = submits[index];
                const {teamId: submitTeamId, created } = submit;
                if (teamId === submitTeamId) {
                    teamSubmits.push(submit);
                    const task = taskBuffer.shift();
                    activeTasks.push({
                        ...task,
                        from: new Date(created),
                    });
                }
            }
        }

        taskBuffer.sort((a, b) => {
            return a.taskId - b.taskId;
        });

        this.ySize = (activeTasks.length * 12) + 20;

        this.xScale = d3.scaleTime().domain([gameStart, gameEnd]).range([30, 580]);
        this.yScale = d3.scaleLinear().domain([0, activeTasks.length]).range([20, this.ySize - 30]);

        const dots = activeTasks.map((task, index: number) => {
            const { taskId, from } = task;
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
                <g style={{ opacity: (active) ? 1 : 0.1 }} key={index}>
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
        teamId: state.statistics.teamId,
    };
};

export default connect(mapStateToProps, null)(TimeLine);
