import { axisBottom } from 'd3-axis';
import { scaleBand, scaleTime } from 'd3-scale';
import { select } from 'd3-selection';
import { timeMinute } from 'd3-time';
import { ChartComponent } from 'FKSDB/Components/Charts/Core/chart-component';
import { SubmitModel, Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/submit-model';
import { TaskModel } from 'FKSDB/Models/ORM/Models/Fyziklani/task-model';
import * as React from 'react';
import { useSelector } from 'react-redux';
import './timeline.scss';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface ExtendedTask extends TaskModel {
    from: Date;
}

interface OwnProps {
    teamId: number;
}

export default function Timeline({teamId}: OwnProps) {

    const gameEnd = new Date(useSelector((state: Store) => state.timer.gameEnd));
    const gameStart = new Date(useSelector((state: Store) => state.timer.gameStart));
    const submits = useSelector((state: Store) => state.data.submits);
    const tasks = useSelector((state: Store) => state.data.tasks);
    const tasksOnBoard = useSelector((state: Store) => state.data.tasksOnBoard);

    const {activeTasks, teamSubmits} = reconstructTeamGame(submits, tasks, tasksOnBoard, gameStart, teamId);

    const innerYSize = activeTasks.length * 30;
    const ySize = innerYSize + ChartComponent.margin.top + ChartComponent.margin.top;
    const xScale = scaleTime<number, number>().domain([gameStart, gameEnd]).range(ChartComponent.getInnerXSize());

    const yScale = scaleBand().domain(activeTasks.map(task => task.label)).range([0, innerYSize]).padding(0.2);

    const bars = activeTasks.map((task, index: number) => {
        const {taskId, from} = task;
        const submit = teamSubmits.filter(fSubmit => fSubmit.taskId === taskId)[0];
        const to = submit ? new Date(submit.modified) : gameEnd;
        let fromCoordinates;
        let toCoordinates;
        if (from < to) {
            fromCoordinates = xScale(from);
            toCoordinates = xScale(to);
        } else {
            fromCoordinates = xScale(to);
            toCoordinates = xScale(from);
        }
        const yCoordinates = yScale(task.label);
        return <g
            className=""
            key={index}
            transform={'translate(' + fromCoordinates + ',' + yCoordinates + ')'}
            style={{'--color': submit ? ('var(--color-fof-points-' + submit.points + ')') : '#ccc'} as React.CSSProperties}
        >
            <rect
                height={yScale.bandwidth()}
                width={toCoordinates - fromCoordinates}
            />
            <text
                x={(toCoordinates - fromCoordinates) / 2}
                y={yScale.bandwidth() / 2}
            >{task.label}</text>
        </g>;
    });

    return <div className="chart-game-team-timeline">
        <svg viewBox={'0 0 ' + ChartComponent.size.width + ' ' + ySize} className="chart">
            <g transform={'translate(0,' + (ySize - ChartComponent.margin.bottom) + ')'} className="x axis"
               ref={(xAxisRef) => {
                   const xAxis = axisBottom<Date>(xScale).tickSizeInner(-ySize).tickArguments([timeMinute.every(30)]);
                   select<SVGGElement, null>(xAxisRef).call(xAxis);
               }}/>
            <g className="bars">
                {bars}
            </g>
        </svg>
    </div>;
}

const reconstructTeamGame = (submits: Submits, tasks: TaskModel[], tasksOnBoard: number, gameStart: Date, teamId: number):
    { activeTasks: ExtendedTask[]; teamSubmits: SubmitModel[] } => {
    const taskBuffer = [...(tasks.slice(tasksOnBoard))];
    const teamSubmits = [];
    const activeTasks: ExtendedTask[] = [];

    for (let i = 0; i < tasksOnBoard; i++) {
        activeTasks.push({
            ...tasks[i],
            from: gameStart,
        });
    }
    const submitArray: SubmitModel[] = Object.values(submits);
    submitArray
        .sort((a, b) => (new Date(a.modified)).getTime() - (new Date(b.modified)).getTime())
        .forEach((submit) => {
            const {teamId: submitTeamId, modified} = submit;
            if (teamId === submitTeamId) {
                if (submit.points !== null && submit.points !== 0) {
                    teamSubmits.push(submit);
                    const task = taskBuffer.shift();
                    activeTasks.push({
                        ...task,
                        from: new Date(modified),
                    });
                }
            }
        });
    taskBuffer.sort((a, b) => {
        return a.taskId - b.taskId;
    });

    return {activeTasks, teamSubmits};
};
