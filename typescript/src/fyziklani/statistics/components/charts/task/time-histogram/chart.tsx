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
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { Store as StatisticsStore } from '../../../../reducers';

interface State {
    submits?: Submits;
    fromDate?: Date;
    toDate?: Date;
    activePoints?: number;
    aggregationTime?: number;
}

interface Props {
    taskId: number;
    availablePoints: number[];
}

class TimeHistogram extends React.Component<State & Props, {}> {

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
        const taskTimeSubmits: {
            [time: number]: {
                [points: number]: number;
            };
        } = {};
        const {
            toDate,
            fromDate,
            taskId,
            submits,
            aggregationTime,
            activePoints,
            availablePoints,
        } = this.props;

        for (const index in submits) {
            if (submits.hasOwnProperty(index)) {
                const submit: Submit = submits[index];
                if (submit.taskId === taskId) {
                    if (submit.points > 0) {
                        if (!activePoints || activePoints === submit.points) {
                            const ms = (new Date(submit.created)).getTime();
                            const c = Math.floor(ms / aggregationTime);
                            taskTimeSubmits[c] = taskTimeSubmits[c] || {1: 0, 2: 0, 3: 0, 5: 0};
                            taskTimeSubmits[c][submit.points]++;
                        }
                    }
                }
            }
        }
        let maxPoints = 0;
        for (const key in taskTimeSubmits) {
            if (taskTimeSubmits.hasOwnProperty(key)) {
                const item = taskTimeSubmits[key];
                const sum = item[1] + item[2] + item[3] + item[5];
                maxPoints = maxPoints < sum ? sum : maxPoints;
            }
        }
        this.yScale = scaleLinear<number, number>().domain([0, maxPoints]).range([370, 20]);
        this.xScale = scaleTime().domain([fromDate, toDate]).range([30, 580]);

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
                        key={index} points={[[x1, y1], [x1, y2], [x2, y2], [x2, y1]].join(' ')}
                        fill={getColorByPoints(points)}/>);
                });

                bars.push(<g key={key}>
                    {polygons}
                </g>);
            }
        }
        return (
            <svg viewBox="0 0 600 400" className="chart time-histogram">
                <g>
                    {bars}
                    <g transform="translate(0,370)" className="x axis" ref={(xAxis) => this.xAxis = xAxis}/>
                    <g transform="translate(30,0)" className="x axis" ref={(yAxis) => this.yAxis = yAxis}/>
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

const mapStateToProps = (state: StatisticsStore): State => {
    return {
        activePoints: state.statistics.activePoints,
        aggregationTime: state.statistics.aggregationTime,
        fromDate: state.statistics.fromDate,
        submits: state.data.submits,
        toDate: state.statistics.toDate,
    };
};

export default connect(mapStateToProps, null)(TimeHistogram);
