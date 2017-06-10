import * as d3 from 'd3';

export const getPieData = (data: any): any => {
    return d3.pie().value((item: any) => {
        return +item.count;
    })(data);
};

export const getColorByPoints = (points: number): string => {
    switch (points) {
        case 5:
            return 'aquamarine';
        case 3:
            return 'greenyellow';
        case 2:
            return 'orange';
        case 1:
            return 'red';
        default:
            return 'gray';
    }
};
