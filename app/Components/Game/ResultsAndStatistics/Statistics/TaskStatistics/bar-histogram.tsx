import { axisBottom, axisLeft } from 'd3-axis';
import { scaleLinear, scaleTime } from 'd3-scale';
import { select } from 'd3-selection';
import { ChartComponent } from 'FKSDB/Components/Charts/Core/chart-component';
import * as React from 'react';
import { useSelector } from 'react-redux';
import { submitsByTask } from '../Middleware/submits-by-task';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

interface OwnProps {
    taskId: number;
    availablePoints: number[];
}

export default function BarHistogram({taskId, availablePoints}: OwnProps) {
    const aggregationTime = useSelector((state: Store) => state.statistics.aggregationTime);
    const start = useSelector((state: Store) => state.timer.gameStart);
    const submits = useSelector((state: Store) => state.data.submits);
    const end = useSelector((state: Store) => state.timer.gameEnd);
    const taskTimeSubmits = submitsByTask(submits, taskId, aggregationTime);

    let maxPoints = 0;
    for (const key in taskTimeSubmits) {
        if (Object.hasOwn(taskTimeSubmits, key)) {
            const item = taskTimeSubmits[key];
            const sum = availablePoints.reduce<number>((prev, current) => {
                return prev + item[current];
            }, 0);
            maxPoints = maxPoints < sum ? sum : maxPoints;
        }
    }
    const yScale = scaleLinear<number, number>().domain([0, maxPoints]).range(ChartComponent.getInnerYSize());
    const xScale = scaleTime().domain([start, end]).range(ChartComponent.getInnerXSize());
    const bars = [];
    for (const key in taskTimeSubmits) {
        if (Object.hasOwn(taskTimeSubmits, key)) {
            const item = taskTimeSubmits[key];
            const ms = +key * aggregationTime;
            const x1 = xScale(new Date(ms)) + 2;
            const x2 = xScale(new Date(ms + aggregationTime)) - 2;

            let sum = 0;
            const polygons = [];
            let y1 = yScale(0);
            availablePoints.forEach((points, index) => {
                sum += item[points];
                const y2 = yScale(sum);
                polygons.push(<polygon
                    key={index}
                    points={[[x1, y1], [x1, y2], [x2, y2], [x2, y1]].join(' ')}
                    data-points={points}
                    style={{'--bar-color': 'var(--color-fof-points-' + points + ')'} as React.CSSProperties}
                />);
                y1 = y2;
            });

            bars.push(<g key={key}>
                {polygons}
            </g>);
        }
    }
    return <div className="bar-histogram">
        <svg viewBox={ChartComponent.getViewBox()} className="chart">
            <g>
                {bars}
                <g transform={ChartComponent.transformXAxis()} className="x-axis" ref={(xAxisRef) => {
                    const xAxis = axisBottom<Date>(xScale);
                    select(xAxisRef).call(xAxis);
                }
                }/>
                <g transform={ChartComponent.transformYAxis()} className="y-axis"
                   ref={(yAxisRef) => {
                       const yAxis = axisLeft<number>(yScale);
                       select(yAxisRef).call(yAxis);
                   }}/>
            </g>
        </svg>
    </div>;
}
