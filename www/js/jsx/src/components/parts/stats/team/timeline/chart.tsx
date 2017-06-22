import * as React from 'react';
import {connect} from 'react-redux';
import * as d3 from 'd3';

import {
    ISubmit,
    ITask,
} from '../../../../../helpers/interfaces';
import {
    ScaleLinear,
    ScaleTime,
} from 'd3-scale';

import {getColorByPoints} from '../../../../../helpers/pie/index';

interface IProps {
    submits?: any;
    tasks?: Array<ITask>;
    teamID?: number;
    gameStart?: Date;
    gameEnd?: Date;
}

class TimeLine extends React.Component<IProps, void> {

    private xAxis: any;
    private ySize: number;

    private xScale: ScaleTime<any, any>;
    private yScale: ScaleLinear<any, any>;

    componentDidMount() {
        this.getAxis();
    }

    componentDidUpdate() {
        this.getAxis();
    }

    private getAxis() {
        const xAxis = d3.axisBottom(this.xScale).tickSizeInner(-this.ySize).tickArguments([d3.timeMinute.every(30)]);
        d3.select(this.xAxis).call(xAxis);
    }

    render() {
        const {teamID, submits, tasks, gameStart, gameEnd} = this.props;
        const taskOnBoar = 7;
        const taskBuffer = [...(tasks.slice(taskOnBoar))];

        const activeTasks = [];

        for (let i = 0; i < taskOnBoar; i++) {
            activeTasks.push({
                ...tasks[i],
                from: gameStart,
            });
        }
        const teamSubmits = [];
        for (let index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: ISubmit = submits[index];
                const {team_id, created} = submit;
                if (teamID === team_id) {
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
            return a.task_id - b.task_id;
        });

        this.ySize = (activeTasks.length * 12) + 20;

        this.xScale = d3.scaleTime().domain([gameStart, gameEnd]).range([30, 580]);
        this.yScale = d3.scaleLinear().domain([0, activeTasks.length]).range([20, this.ySize - 30]);

        const dots = activeTasks.map((task, index) => {
                const {task_id, from} = task;
                const submit = teamSubmits.filter((fSubmit) => {
                    return fSubmit.task_id === task_id;
                })[0];
                const to = submit ? new Date(submit.created) : gameEnd;

                const fromCoordinates = this.xScale(from);
                const toCoordinates = this.xScale(to);
                const yCoordinates = this.yScale(index);

                const color = getColorByPoints(submit ? submit.points : null);
                return (
                    <g>
                        <polyline
                            points={'' + fromCoordinates + ',' + yCoordinates + ' ' + toCoordinates + ',' + yCoordinates}
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
            }
        );

        return (
            <div className="col-lg-12">
                <svg viewBox={'0 0 600 ' + this.ySize} className="chart time-line">
                    <g transform={'translate(0,' + (this.ySize - 30) + ')'} className="x axis"
                       ref={(xAxis) => this.xAxis = xAxis}/>
                    {dots}
                </svg>
            </div>
        );
    }
}

const mapStateToProps = (state, ownProps: IProps): IProps => {
    return {
        ...ownProps,
        submits: state.results.submits,
        tasks: state.results.tasks,
        gameStart: state.timer.gameStart,
        gameEnd: state.timer.gameEnd,
        teamID: state.stats.teamID,
    };
};

export default connect(mapStateToProps, null)(TimeLine);
