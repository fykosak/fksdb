import { CurveFactory } from 'd3-shape';
import { PointData } from '@apps/fyziklaniResults/statistics/middleware/charts/lines';

export type LineChartData = Array<{
    name: string;
    description?: string;
    color: string;
    display: {
        points?: boolean;
        lines?: boolean;
        area?: boolean;
    };
    curveFactory?: CurveFactory;
    points: Array<ExtendedPointData<Date | number>>;
}>;

export interface ExtendedPointData<T> extends PointData<T> {
    active?: boolean;
    color?: string;
    label?: string;
}
