import { axisBottom, axisLeft } from 'd3-axis';
import { ScaleLinear } from 'd3-scale';
import { select, selectAll } from 'd3-selection';
import ChartComponent from 'FKSDB/Components/Charts/Core/ChartComponent';
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
    display?: {
        xGrid: boolean;
        yGrid: boolean;
    };
}

export default class BarHistogram extends ChartComponent<OwnProps, Record<string, never>> {

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
                    fill={item.color}>
                    <title>{item.yValue}</title>
                    </polygon>
                );
            });
            bars.push(rows);
        });
        return <svg viewBox={this.getViewBox()} className="chart">
            <g>
                <g transform={this.transformXAxis()} className="axis x-axis" ref={(xAxis) => this.xAxis = xAxis}/>
                <g transform={this.transformYAxis()} className="axis y-axis" ref={(yAxis) => this.yAxis = yAxis}/>
                {bars}
            </g>
        </svg>;
    }

    private getAxis(): void {
        const {xScale, yScale, display} = this.props;
        const xAxis = axisBottom<number>(xScale);
        const yAxis = axisLeft<number>(yScale);
        xAxis.tickValues(this.props.data.map((group) => group.xValue).map((value) => {
            return +value;
        }));

        select(this.xAxis).call(xAxis);
        select(this.yAxis).call(yAxis);

        if (display && display.xGrid) {
            selectAll(".x-axis g.tick")
                .append("line").lower()
                .attr("class","grid-line")
                .attr("y2",(-this.size.height + this.margin.top + this.margin.bottom))
                .attr("stroke","currentcolor");
        }
        if (display && display.yGrid) {
            selectAll(".y-axis g.tick")
                .append("line").lower()
                .attr("class","grid-line")
                .attr("x2",(this.size.width - this.margin.left - this.margin.right))
                .attr("stroke","currentcolor");
        }
    }
}
