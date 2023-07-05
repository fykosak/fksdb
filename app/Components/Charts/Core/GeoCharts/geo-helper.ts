export interface GeoData {
    [countryISO: string]: number;
}

export const findMax = (data: GeoData): number => {
    let max = 0;
    for (const country in data) {
        if (Object.hasOwn(data,country)) {
            const datum = data[country];
            max = max > datum ? max : datum;
        }
    }
    return max;
};
