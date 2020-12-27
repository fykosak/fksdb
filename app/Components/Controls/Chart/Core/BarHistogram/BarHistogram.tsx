import ChartComponent from 'FKSDB/Components/Controls/Chart/Core/ChartComponent';
import { axisBottom, axisLeft } from 'd3-axis';
import { ScaleLinear } from 'd3-scale';
import { select } from 'd3-selection';
import * as React from 'react';

interface OwnProps {
    xScale: ScaleLinear<number, number>;
    yScale: ScaleLinear<number, number>;
    data: Array<{
        xValue: number;
        items: Array<{
            yValue: number;
            label: string;
            color: string;
        }>;
    }>;
}

export default class BarHistogram extends ChartComponent<OwnProps, {}> {

    private xAxis: SVGGElement;
    private yAxis: SVGGElement;

    public componentDidMount() {
        this.getAxis();
    }

    public componentDidUpdate() {
        this.getAxis();
    }

    public render() {
        const {data, yScale, xScale} = this.props;
        yScale.range(this.getInnerYSize());
        xScale.range(this.getInnerXSize());

        let maxLength = 0;
        data.forEach((group) => {
            maxLength = maxLength > group.items.length ? maxLength : group.items.length;
        });

        const barXSize = 0.8 / (maxLength + 2);
        const bars = [];

        data.forEach((group) => {
            const relativeX = +group.xValue - 0.4;
            const rows = [];
            group.items.forEach((item, index) => {
                const x1 = this.props.xScale(relativeX + barXSize * +index);
                const x2 = this.props.xScale(relativeX + barXSize * (+index + 1));
                const y1 = this.props.yScale(item.yValue);
                const y2 = this.props.yScale(0);
                rows.push(<polygon
                    key={index}
                    points={[[x1, y1], [x1, y2], [x2, y2], [x2, y1]].join(' ')}
                    fill={item.color}/>);
            });
            bars.push(rows);
        });
        return <svg viewBox={this.getViewBox()} className="chart">
            <g>
                {bars}
                <g transform={this.transformXAxis()} className="axis x-axis" ref={(xAxis) => this.xAxis = xAxis}/>
                <g transform={this.transformYAxis()} className="axis y-axis" ref={(yAxis) => this.yAxis = yAxis}/>
            </g>
        </svg>;
    }

    private getAxis(): void {
        const xAxis = axisBottom<number>(this.props.xScale);
        xAxis.tickValues(this.props.data.map((group) => group.xValue).map((value) => {
            return +value;
        }));
        select(this.xAxis).call(xAxis);

        const yAxis = axisLeft<number>(this.props.yScale);
        select(this.yAxis).call(yAxis);
    }
}
