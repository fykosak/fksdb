import { pie } from 'd3-shape';

export function getPieData<D>(data: any): Array<d3.PieArcDatum<D>> {
    return pie<any, D>().value((item: any) => {
        return +item.count;
    })(data);
}
