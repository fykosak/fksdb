export const getColorByPoints = (points: number): string => {
    switch (points) {
        case 5:
            return 'limegreen';
        case 3:
            return 'gold';
        case 2:
            return 'orange';
        case 1:
            return 'red';
        default:
            return 'gray';
    }
};
