import {
    ScaleLinear,
    ScaleTime,
} from 'd3-scale';
import {
    area,
    CurveFactory,
    curveLinear,
    line,
} from 'd3-shape';
import { IExtendedSubmit } from '../../components/charts/team/line-chart/chart';

interface IScales {
    xScale: ScaleTime<number, number>;
    yScale: ScaleLinear<number, number>;
}

export interface IPointData {
    created: string;
    totalPoints: number;
}

export function getLinePath(scales: IScales, data: IPointData[], curve: CurveFactory = curveLinear): string {
    const {xScale, yScale} = scales;
    return line<IPointData>()
        .x((element: IExtendedSubmit) => {
            return xScale(new Date(element.created));
        })
        .y((element: IExtendedSubmit) => {
            return yScale(element.totalPoints);
        })
        .curve(curve)(data);
}

export function getAreaPath(scales: IScales, data: IPointData[], y0: number, curve: CurveFactory = curveLinear): string {
    const {xScale, yScale} = scales;
    return area<IPointData>()
        .x((element: IExtendedSubmit) => {
            return xScale(new Date(element.created));
        }).y0(y0)
        .y1((element: IExtendedSubmit) => {
            return yScale(element.totalPoints);
        }).curve(curve)(data);
}
