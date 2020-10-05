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

export interface PointData<X = Date | number> {
    xValue: X;
    yValue: number;
}

export function getLinePath(
    xScale: ScaleTime<number, number> | ScaleLinear<number, number>,
    yScale: ScaleLinear<number, number>,
    data: PointData[],
    curve: CurveFactory = curveLinear,
): string {
    return line<PointData>()
        .x((element: PointData) => {
            if (element.xValue instanceof Date) {
                return xScale(new Date(element.xValue));
            }
            return xScale(element.xValue);
        })
        .y((element: PointData) => {
            return yScale(element.yValue);
        })
        .curve(curve)(data);
}

export function getAreaPath(
    xScale: ScaleTime<number, number> | ScaleLinear<number, number>,
    yScale: ScaleLinear<number, number>,
    data: PointData[],
    y0: number,
    curve: CurveFactory = curveLinear,
): string {
    return area<PointData>()
        .x((element) => {
            if (element.xValue instanceof Date) {
                return xScale(new Date(element.xValue));
            }
            return xScale(element.xValue);
        })
        .y0(y0)
        .y1((element) => {
            return yScale(element.yValue);
        }).curve(curve)(data);
}
