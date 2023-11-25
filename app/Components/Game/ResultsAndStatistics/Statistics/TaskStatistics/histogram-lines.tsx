import { scaleLinear, scaleTime } from 'd3-scale';
import { curveMonotoneX } from 'd3-shape';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/line-chart';
import { LineChartData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';
import { useSelector } from 'react-redux';
import { submitsByTask } from '../Middleware/submits-by-task';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface OwnProps {
    taskId: number;
    availablePoints: number[];
}

export default function HistogramLines({taskId, availablePoints}: OwnProps) {

    const aggregationTime = useSelector((state: Store) => state.statistics.aggregationTime);
    const gameEnd = useSelector((state: Store) => state.timer.gameEnd);
    const gameStart = useSelector((state: Store) => state.timer.gameStart);
    const submits = useSelector((state: Store) => state.data.submits);
    const taskTimeSubmits = submitsByTask(submits, taskId, aggregationTime);

    let i = Math.floor(gameStart.getTime() / aggregationTime);
    let safeCount = 0;
    while (i < Math.floor(gameEnd.getTime() / aggregationTime)) {
        taskTimeSubmits[i] = taskTimeSubmits[i] || {1: 0, 2: 0, 3: 0, 5: 0};
        i++;
        safeCount++;
        if (safeCount > 200) {
            throw Error('Safe counter!!!');
        }
    }
    let maxPoints = 0;
    for (const key in taskTimeSubmits) {
        if (Object.hasOwn(taskTimeSubmits, key)) {
            const item = taskTimeSubmits[key];
            availablePoints.map((points) => {
                maxPoints = maxPoints < item[points] ? item[points] : maxPoints;
            });
        }
    }
    const yScale = scaleLinear<number, number>().domain([0, maxPoints]);
    const xScale = scaleTime().domain([gameStart, gameEnd]);

    const pointsData = {
        1: [],
        2: [],
        3: [],
        5: [],
    };
    for (const key in taskTimeSubmits) {
        if (Object.hasOwn(taskTimeSubmits, key)) {

            const item = taskTimeSubmits[key];
            const ms = +key * aggregationTime;
            const timeString = new Date(ms + (aggregationTime / 2));

            availablePoints.forEach((points) => {
                pointsData[points].push({
                    xValue: timeString,
                    yValue: item[points],
                });
            });
        }
    }
    const lineChartData: LineChartData<Date> = [];
    availablePoints.forEach((points) => {
        lineChartData.push({
            color: 'var(--color-fof-points-' + points + ')',
            curveFactory: curveMonotoneX,
            display: {
                area: true,
                lines: true,
                points: false,
            },
            name: points.toString(),
            points: [
                {
                    xValue: gameStart,
                    yValue: 0,
                },
                ...pointsData[points],
                {
                    xValue: gameEnd,
                    yValue: 0,
                }],
        });
    });
    return <LineChart<Date> data={lineChartData} xScale={xScale} yScale={yScale}/>;
}
