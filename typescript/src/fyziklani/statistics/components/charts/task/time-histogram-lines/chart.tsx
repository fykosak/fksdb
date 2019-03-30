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
import { curveMonotoneX } from 'd3-shape';
import * as React from 'react';
import { connect } from 'react-redux';
import {
    Submit,
    Submits,
} from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import {
    getAreaPath,
    getLinePath,
} from '../../../../middleware/charts/lines';
import { Store as StatisticsStore } from '../../../../reducers';

interface State {
    submits?: Submits;
    fromDate?: Date;
    gameStart?: Date;
    gameEnd?: Date;
    toDate?: Date;
    activePoints?: number;
    aggregationTime?: number;
}

interface Props {
    taskId: number;
    availablePoints: number[];
}

class TimeHistogramLines extends React.Component<State & Props, {}> {

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
            fromDate,
            toDate,
            gameStart,
            gameEnd,
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
                        const ms = (new Date(submit.created)).getTime();
                        const c = Math.floor(ms / aggregationTime);
                        taskTimeSubmits[c] = taskTimeSubmits[c] || {1: 0, 2: 0, 3: 0, 5: 0};
                        taskTimeSubmits[c][submit.points]++;
                    }
                }
            }
        }

        let i = Math.floor(gameStart.getTime() / aggregationTime);
        let safeCount = 0;
        while (i < Math.floor(gameEnd.getTime() / aggregationTime)) {
            taskTimeSubmits[i] = taskTimeSubmits[i] || {1: 0, 2: 0, 3: 0, 5: 0};
            i++;
            safeCount++;
            if (safeCount > 200) {
                throw Error('Safe counter!!!');
                break;
            }
        }
        let maxPoints = 0;
        for (const key in taskTimeSubmits) {
            if (taskTimeSubmits.hasOwnProperty(key)) {
                const item = taskTimeSubmits[key];
                availablePoints.map((points) => {
                    if (!activePoints || activePoints === points) {
                        maxPoints = maxPoints < item[points] ? item[points] : maxPoints;
                    }
                });
            }
        }
        this.yScale = scaleLinear<number, number>().domain([0, maxPoints]).range([370, 20]);
        this.xScale = scaleTime().domain([fromDate, toDate]).range([30, 580]);
        const scales = {
            xScale: this.xScale,
            yScale: this.yScale,
        };

        const pointsData = {
            1: [],
            2: [],
            3: [],
            5: [],
        };
        for (const key in taskTimeSubmits) {
            if (taskTimeSubmits.hasOwnProperty(key)) {

                const item = taskTimeSubmits[key];
                const ms = +key * aggregationTime;
                const timeString = (new Date(ms + (aggregationTime / 2))).toString();

                availablePoints.forEach((points) => {
                    pointsData[points].push({
                        created: timeString,
                        totalPoints: item[points],
                    });
                });
            }
        }

        return (
            <svg viewBox="0 0 600 400" className="chart time-line-histogram">
                <g>
                    {availablePoints.map((points, index) => {
                        if (!activePoints || activePoints === points) {
                            const data = [
                                {
                                    created: gameStart.toString(),
                                    totalPoints: 0,
                                },
                                ...pointsData[points],
                                {
                                    created: gameEnd.toString(),
                                    totalPoints: 0,
                                }];
                            const linePath = getLinePath(scales, data, curveMonotoneX);
                            const areaPath = getAreaPath(scales, data, this.yScale(0), curveMonotoneX);

                            return <g key={index}>
                                <path d={linePath} className={'line'} stroke={getColorByPoints(points)}/>
                                <path d={areaPath} className={'area'} fill={getColorByPoints(points)}/>
                            </g>;
                        }
                        return null;

                    })}
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
        gameEnd: new Date(state.timer.gameEnd),
        gameStart: new Date(state.timer.gameStart),
        submits: state.data.submits,
        toDate: state.statistics.toDate,
    };
};

export default connect(mapStateToProps, null)(TimeHistogramLines);
