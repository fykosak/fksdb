import * as d3 from 'd3';

export const getLinePath = ({ xScale, yScale }, data): string => {
    return d3.line()
        .x((element: any) => {
            return xScale(new Date(element.created));
        })
        .y((element: any) => {
            return yScale(element.totalPoints);
        })(data);
};
