import * as d3 from 'd3';
import {
    ScaleLinear,
    ScaleTime,
} from 'd3';
import { IExtendedSubmit } from '../../components/charts/team/line-chart/chart';

interface IScales {
    xScale: ScaleTime<number, number>;
    yScale: ScaleLinear<number, number>;
}

export function getLinePath(scales: IScales, data: IExtendedSubmit[]): string {
    const {xScale, yScale} = scales;
    return d3.line<IExtendedSubmit>()
        .x((element: IExtendedSubmit) => {
            return xScale(new Date(element.created));
        })
        .y((element: IExtendedSubmit) => {
            return yScale(element.totalPoints);
        })(data);
}
