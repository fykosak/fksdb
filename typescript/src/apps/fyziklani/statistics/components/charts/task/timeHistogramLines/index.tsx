import LineChart from '@shared/components/lineChart/lineChart';
import {
    scaleLinear,
    scaleTime,
} from 'd3-scale';
import { curveMonotoneX } from 'd3-shape';
import * as React from 'react';
import { connect } from 'react-redux';
import { Submits } from '../../../../../helpers/interfaces';
import { getColorByPoints } from '../../../../middleware/charts/colors';
import { submitsByTask } from '../../../../middleware/charts/submitsByTask';
import { Store as StatisticsStore } from '../../../../reducers';
import { LineChartData } from '@shared/components/lineChart/interfaces';

interface StateProps {
    submits: Submits;
    fromDate: Date;
    gameStart: Date;
    gameEnd: Date;
    toDate: Date;
    activePoints: number;
    aggregationTime: number;
}

interface OwnProps {
    taskId: number;
    availablePoints: number[];
}

class TimeHistogramLines extends React.Component<StateProps & OwnProps, {}> {

    public render() {
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
            if (taskTimeSubmits.hasOwnProperty(key)) {
                const item = taskTimeSubmits[key];
                availablePoints.map((points) => {
                    if (!activePoints || activePoints === points) {
                        maxPoints = maxPoints < item[points] ? item[points] : maxPoints;
                    }
                });
            }
        }
        const yScale = scaleLinear<number, number>().domain([0, maxPoints]);
        const xScale = scaleTime().domain([fromDate, toDate]);

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
                const timeString = new Date(ms + (aggregationTime / 2));

                availablePoints.forEach((points) => {
                    pointsData[points].push({
                        xValue: timeString,
                        yValue: item[points],
                    });
                });
            }
        }
        const lineChartData: LineChartData = [];
        availablePoints.forEach((points) => {
            if (!activePoints || activePoints === points) {
                lineChartData.push({
                    color: getColorByPoints(points),
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
            }
        });
        return <LineChart data={lineChartData} xScale={xScale} yScale={yScale}/>;
    }
}

const mapStateToProps = (state: StatisticsStore): StateProps => {
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
