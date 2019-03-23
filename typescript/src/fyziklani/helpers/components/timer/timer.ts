export const getCurrentDelta = (toStart: number, toEnd: number, inserted: Date): DeltaTimes => {
    const now = new Date();
    const delta = now.getTime() - inserted.getTime();
    return {
        toEnd: toEnd - delta,
        toStart: toStart - delta,
    };
};

export interface DeltaTimes {
    toStart: number;
    toEnd: number;
}
