import { boxplot, boxplotStats } from 'd3-boxplot';
import { Translator } from '@translator/translator';
import { scaleBand, scaleLinear } from 'd3-scale';
import { ChartComponent } from 'FKSDB/Components/Charts/Core/chart-component';
import { select } from 'd3-selection';
import { axisBottom, axisLeft } from 'd3-axis';
import * as React from 'react';
import './style.scss';

export interface OwnProps {
    data: {
        [key: string]: number[];
    };
    translator: Translator;
}

export default function Chart({data}: OwnProps) {
    const yScale = scaleLinear<number, number>().domain([-0.1, 1.5]).range(ChartComponent.getInnerYSize());
    const xScale = scaleBand<string>()
        .paddingInner(0.5)
        .paddingOuter(0.5)
        .domain(Object.keys(data))
        .range(ChartComponent.getInnerXSize());
    const bars = [];
    for (const label in data) {
        const datum = data[label];
        const stats = boxplotStats(datum);
        const plot = boxplot()
            .scale(yScale)
            .vertical(true)
            .boxwidth(xScale.bandwidth())
            .showInnerDots(false)
            .opacity(1);
        bars.push(<g transform={'translate(' + (xScale(label) - 10 + (xScale.bandwidth() / 2)) + ',0)'}
                     data-label={label}
                     key={label} ref={(el) => {
            select(el).datum(stats).call(plot)
        }}/>)
    }

    return <div className="point-variance-chart">
        <svg viewBox={ChartComponent.getViewBox()} className="chart">
            <g>
                <g transform={ChartComponent.transformXAxis()}
                   className="axis x-axis"
                   ref={(xAxisRef) => {
                       select(xAxisRef).call(axisBottom(xScale));
                   }}/>
                <g transform={ChartComponent.transformYAxis()}
                   className="axis y-axis"
                   ref={(yAxisRef) => {
                       select(yAxisRef).call(axisLeft<number>(yScale));
                   }}/>
                {bars}
            </g>
        </svg>
    </div>;
}
