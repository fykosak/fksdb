import { LineChartData } from '@shared/components/lineChart/interfaces';
import LineChart from '@shared/components/lineChart/lineChart';
import LineChartLegend from '@shared/components/lineChart/lineChartLegend';
import { scaleLinear, scaleTime } from 'd3-scale';
import * as React from 'react';
import { PointData } from '../../fyziklani/statistics/middleware/charts/lines';

interface Data {
    created: string;
    gender: 'M' | 'F';
    personId: number;
}

interface OwnProps {
    data: Data[];
}

export default class TotalPersons extends React.Component<OwnProps, {}> {

    public render() {
        const {data} = this.props;
        const lineChartData: LineChartData = [];
        const pointsAll: PointData[] = [];
        const pointsMale: PointData[] = [];
        const pointsFemale: PointData[] = [];
        const pointsPersonId: PointData[] = [];

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

        lineChartData.push({
            color: 'gray',
            display: {
                area: false,
                lines: true,
                points: false,
            },
            name: 'All',
            points: pointsAll,
        });

        lineChartData.push({
            color: '#1175da',
            display: {
                area: false,
                lines: true,
                points: false,
            },
            name: 'Male',
            points: pointsMale,
        });

        lineChartData.push({
            color: '#da1175',
            display: {
                area: false,
                lines: true,
                points: false,
            },
            name: 'Female',
            points: pointsFemale,
        });
        lineChartData.push({
            color: '#da7511',
            display: {
                area: false,
                lines: true,
                points: false,
            },
            name: 'person Id',
            points: pointsPersonId,
        });

        const yScale = scaleLinear<number, number>().domain([0, data[data.length - 1].personId]);
        const xScale = scaleTime().domain([new Date(data[0].created), new Date()]);

        return <div className="row">
            <div className="chart-container col-lg-8">
                <LineChart data={lineChartData} xScale={xScale} yScale={yScale} display={{xGrid: true, yGrid: true}}/>
            </div>
            <div className="chart-legend-container col-lg-4">
                <LineChartLegend data={lineChartData}/>
            </div>
        </div>;
    }
}
