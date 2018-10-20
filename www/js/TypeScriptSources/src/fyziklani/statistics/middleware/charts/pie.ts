import * as d3 from 'd3';

export function getPieData<D>(data: any): Array<d3.PieArcDatum<D>> {
    return d3.pie<any, D>().value((item: any) => {
        return +item.count;
    })(data);
}
