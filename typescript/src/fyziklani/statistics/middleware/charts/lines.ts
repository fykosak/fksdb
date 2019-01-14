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
import { ExtendedSubmit } from '../../components/charts/team/line-chart/chart';

interface Scales {
    xScale: ScaleTime<number, number>;
    yScale: ScaleLinear<number, number>;
}

export interface PointData {
    created: string;
    totalPoints: number;
}

export function getLinePath(scales: Scales, data: PointData[], curve: CurveFactory = curveLinear): string {
    const {xScale, yScale} = scales;
    return line<PointData>()
        .x((element: ExtendedSubmit) => {
            return xScale(new Date(element.created));
        })
        .y((element: ExtendedSubmit) => {
            return yScale(element.totalPoints);
        })
        .curve(curve)(data);
}

export function getAreaPath(scales: Scales, data: PointData[], y0: number, curve: CurveFactory = curveLinear): string {
    const {xScale, yScale} = scales;
    return area<PointData>()
        .x((element: ExtendedSubmit) => {
            return xScale(new Date(element.created));
        }).y0(y0)
        .y1((element: ExtendedSubmit) => {
            return yScale(element.totalPoints);
        }).curve(curve)(data);
}
