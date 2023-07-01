import { axisBottom, axisLeft } from 'd3-axis';
import { ScaleLinear, scaleLinear, ScaleTime, scaleTime } from 'd3-scale';
import { select } from 'd3-selection';
import ChartComponent from 'FKSDB/Components/Charts/Core/ChartComponent';
import { Submits } from 'FKSDB/Models/ORM/Models/Fyziklani/SubmitModel';
import * as React from 'react';
import { connect } from 'react-redux';
import { submitsByTask } from '../Middleware/submitsByTask';
import { Store } from 'FKSDB/Components/Game/ResultsAndStatistics/reducers/store';

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

class BarHistogram extends ChartComponent<StateProps & OwnProps, Record<string, never>> {

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
            if (Object.hasOwn(taskTimeSubmits, key)) {
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
            if (Object.hasOwn(taskTimeSubmits, key)) {
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
                        data-points={points}
                        style={{'--bar-color': 'var(--color-fof-points-' + points + ')'} as React.CSSProperties}
                    />);
                });

                bars.push(<g key={key}>
                    {polygons}
                </g>);
            }
        }
        return <div className="bar-histogram">
            <svg viewBox={this.getViewBox()} className="chart">
                <g>
                    {bars}
                    <g transform={this.transformXAxis()} className="x-axis" ref={(xAxis) => this.xAxis = xAxis}/>
                    <g transform={this.transformYAxis()} className="y-axis" ref={(yAxis) => this.yAxis = yAxis}/>
                </g>
            </svg>
        </div>;
    }

    private getAxis(): void {
        const xAxis = axisBottom<Date>(this.xScale);
        select(this.xAxis).call(xAxis);

        const yAxis = axisLeft<number>(this.yScale);
        select(this.yAxis).call(yAxis);
    }
}

const mapStateToProps = (state: Store): StateProps => {
    return {
        activePoints: state.statistics.activePoints,
        aggregationTime: state.statistics.aggregationTime,
        fromDate: state.timer.gameStart,
        submits: state.data.submits,
        toDate: state.timer.gameEnd,
    };
};

export default connect(mapStateToProps, null)(BarHistogram);
