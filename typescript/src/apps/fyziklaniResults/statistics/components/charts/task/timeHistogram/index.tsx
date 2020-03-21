import AbstractChart from '@shared/components/chart';
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
    Submits,
} from '../../../../../../fyziklani/helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { submitsByTask } from '../../../../middleware/charts/submitsByTask';
import { Store as StatisticsStore } from '../../../../reducers';

interface StateProps {
    submits: Submits;
    fromDate: Date;
    toDate: Date;
    activePoints: number;
    aggregationTime: number;
}

interface OwnProps {
    taskId: number;
    availablePoints: number[];
}

class TimeHistogram extends AbstractChart<StateProps & OwnProps, {}> {

    private xAxis: SVGGElement;
    private yAxis: SVGGElement;

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
            toDate,
            fromDate,
            taskId,
            submits,
            aggregationTime,
            activePoints,
            availablePoints,
        } = this.props;
        const taskTimeSubmits = submitsByTask(submits, taskId, aggregationTime, activePoints);

        let maxPoints = 0;
        for (const key in taskTimeSubmits) {
            if (taskTimeSubmits.hasOwnProperty(key)) {
                const item = taskTimeSubmits[key];
                const sum = availablePoints.reduce<number>((prev, current) => {
                    return prev + item[current];
                }, 0);
                maxPoints = maxPoints < sum ? sum : maxPoints;
            }
        }
        this.yScale = scaleLinear<number, number>().domain([0, maxPoints]).range(this.getInnerYSize());
        this.xScale = scaleTime().domain([fromDate, toDate]).range(this.getInnerXSize());

        const bars = [];
        for (const key in taskTimeSubmits) {
            if (taskTimeSubmits.hasOwnProperty(key)) {
                const item = taskTimeSubmits[key];
                const ms = +key * aggregationTime;
                const x1 = this.xScale(new Date(ms)) + 2;
                const x2 = this.xScale(new Date(ms + aggregationTime)) - 2;

                let sum = 0;
                const polygons = [];
                availablePoints.forEach((points, index) => {
                    const y1 = this.yScale(sum);
                    sum += item[points];
                    const y2 = this.yScale(sum);
                    polygons.push(<polygon
                        key={index}
                        points={[[x1, y1], [x1, y2], [x2, y2], [x2, y1]].join(' ')}
                        fill={getColorByPoints(points)}/>);
                });

                bars.push(<g key={key}>
                    {polygons}
                </g>);
            }
        }
        return (
            <svg viewBox={this.getViewBox()} className="chart time-histogram">
                <g>
                    {bars}
                    <g transform={this.transformXAxis()} className="x-axis" ref={(xAxis) => this.xAxis = xAxis}/>
                    <g transform={this.transformYAxis()} className="y-axis" ref={(yAxis) => this.yAxis = yAxis}/>
                </g>
            </svg>
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
        aggregationTime: state.statistics.aggregationTime,
        fromDate: state.statistics.fromDate,
        submits: state.data.submits,
        toDate: state.statistics.toDate,
    };
};

export default connect(mapStateToProps, null)(TimeHistogram);
