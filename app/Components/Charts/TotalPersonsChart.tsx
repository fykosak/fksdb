import { translator } from '@translator/translator';
import {
    scaleLinear,
    scaleTime,
} from 'd3-scale';
import ChartContainer from 'FKSDB/Components/Charts/Core/ChartContainer';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/LineChart';
import LineChartLegend from 'FKSDB/Components/Charts/Core/LineChart/LineChartLegend';
import { LineChartData, PointData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';

interface Data {
    created: string;
    gender: 'M' | 'F';
    personId: number;
}

interface OwnProps {
    data: Data[];
}

export default class TotalPersonsChart extends React.Component<OwnProps> {

    public render() {
        const {data} = this.props;
        const lineChartData: LineChartData<Date> = [];
        const pointsAll: PointData<Date>[] = [];
        const pointsMale: PointData<Date>[] = [];
        const pointsFemale: PointData<Date>[] = [];
        const pointsPersonId: PointData<Date>[] = [];

        let maleIndex = 0;
        let femaleIndex = 0;
        data.forEach((person, index) => {
            const date = new Date(person.created);
            pointsAll.push({
                xValue: date,
                yValue: index,
            });
            pointsPersonId.push({
                xValue: date,
                yValue: person.personId,
            });
            if (person.gender === 'M') {
                maleIndex++;
                pointsMale.push({
                    xValue: date,
                    yValue: maleIndex,
                });
            } else {
                femaleIndex++;
                pointsFemale.push({
                    xValue: date,
                    yValue: femaleIndex,
                });
            }
        });
        const display = {
            area: false,
            lines: true,
            points: false,
        };
        lineChartData.push({
            color: 'gray',
            display,
            name: translator.getText('All'),
            points: pointsAll,
        });

        lineChartData.push({
            color: '#1175da',
            display,
            name: translator.getText('Male'),
            points: pointsMale,
        });

        lineChartData.push({
            color: '#da1175',
            display,
            name: translator.getText('Female'),
            points: pointsFemale,
        });
        lineChartData.push({
            color: '#da7511',
            display,
            name: translator.getText('Person Id'),
            points: pointsPersonId,
        });

        const yScale = scaleLinear<number, number>().domain([0, data[data.length - 1].personId]);
        const xScale = scaleTime().domain([new Date(data[0].created), new Date()]);
        return <ChartContainer
            chart={LineChart}
            chartProps={{
                data: lineChartData,
                display: {xGrid: true, yGrid: true},
                xScale,
                yScale,
            }}
            legendComponent={LineChartLegend}
            legendProps={{data: lineChartData}}
        />;
    }
}
