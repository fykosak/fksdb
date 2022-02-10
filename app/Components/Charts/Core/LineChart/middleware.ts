import { ScaleLinear, ScaleTime } from 'd3-scale';
import { area, CurveFactory, curveLinear, line } from 'd3-shape';
import { ReactNode } from 'react';

export type LineChartData<XValue extends Date | number> = LineChartDatum<XValue>[];

export type LineChartDatum<XValue extends Date | number> = {
    name: ReactNode;
    description?: string;
    color?: string;
    display: {
        points?: boolean;
        lines?: boolean;
        area?: boolean;
    };
    curveFactory?: CurveFactory;
    points: Array<ExtendedPointData<XValue>>;
};

export interface ExtendedPointData<XValue extends Date | number> extends PointData<XValue> {
    active?: boolean;
    color?: string;
    label?: ReactNode;
}

export interface PointData<XValue extends Date | number> {
    xValue: XValue;
    yValue: number;
}

export const getLinePath = <XValue extends Date | number>(
    xScale: XValue extends Date ? ScaleTime<number, number> : ScaleLinear<number, number>,
    yScale: ScaleLinear<number, number>,
    data: PointData<XValue>[],
    curve: CurveFactory = curveLinear,
): string => {
    return line<PointData<XValue>>()
        .x((element) => {
            return xScale(element.xValue);
        })
        .y((element) => {
            return yScale(element.yValue);
        })
        .curve(curve)(data);
}

export const getAreaPath = <XValue extends Date | number>(
    xScale: XValue extends Date ? ScaleTime<number, number> : ScaleLinear<number, number>,
    yScale: ScaleLinear<number, number>,
    data: PointData<XValue>[],
    y0: number,
    curve: CurveFactory = curveLinear,
): string => {
    return area<PointData<XValue>>()
        .x((element) => {
            return xScale(element.xValue);
        })
        .y0(y0)
        .y1((element) => {
            return yScale(element.yValue);
        })
        .curve(curve)(data);
}
