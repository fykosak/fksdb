import * as d3 from 'd3';

export const getPieData = (data: any): any => {
    return d3.pie().value((item: any) => {
        return +item.count;
    })(data);
};
