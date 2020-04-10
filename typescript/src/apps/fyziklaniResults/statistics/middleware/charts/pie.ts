import {
    pie,
    PieArcDatum,
} from 'd3-shape';

export function getPieData<D extends { count: number }>(data: D[]): Array<PieArcDatum<D>> {
    return pie<any, D>().value((item: D) => {
        return +item.count;
    })(data);
}
