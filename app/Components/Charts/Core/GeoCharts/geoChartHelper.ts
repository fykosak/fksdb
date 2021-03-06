export interface GeoData {
    [countryISO: string]: {
        count: number;
    };
}

export const findMax = (data: GeoData): number => {
    let max = 0;
    for (const country in data) {
        if (data.hasOwnProperty(country)) {
            const datum = data[country];
            max = max > datum.count ? max : datum.count;
        }
    }
    return max;
};
