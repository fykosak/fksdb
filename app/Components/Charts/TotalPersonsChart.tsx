import { scaleLinear, scaleTime } from 'd3-scale';
import ChartContainer from 'FKSDB/Components/Charts/Core/chart-container';
import LineChart from 'FKSDB/Components/Charts/Core/LineChart/line-chart';
import Legend from 'FKSDB/Components/Charts/Core/LineChart/legend';
import { LineChartData, PointData } from 'FKSDB/Components/Charts/Core/LineChart/middleware';
import * as React from 'react';
import { availableLanguage, Translator } from '@translator/translator';

interface Data {
    created: string;
    gender: 'M' | 'F';
    personId: number;
}

interface OwnProps {
    data: Data[];
    translator: Translator<availableLanguage>;
}

export default class TotalPersonsChart extends React.Component<OwnProps, never> {

    public render() {
        const {data, translator} = this.props;
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
            color: 'var(--bs-gray)',
            display,
            name: translator.getText('All'),
            points: pointsAll,
        });

        lineChartData.push({
            color: 'var(--bs-indigo)',
            display,
            name: translator.getText('Male'),
            points: pointsMale,
        });

        lineChartData.push({
            color: 'var(--bs-pink)',
            display,
            name: translator.getText('Female'),
            points: pointsFemale,
        });
        lineChartData.push({
            color: 'var(--bs-yellow)',
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
            legendComponent={Legend}
            legendProps={{data: lineChartData}}
        />;
    }
}
