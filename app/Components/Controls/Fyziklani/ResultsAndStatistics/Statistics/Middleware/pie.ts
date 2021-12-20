import {
    pie,
    PieArcDatum,
} from 'd3-shape';

export const getPieData = <Datum extends { count: number }>(data: Datum[]): Array<PieArcDatum<Datum>> => {
    return pie<Datum>().value((item: Datum) => {
        return +item.count;
    })(data);
}
