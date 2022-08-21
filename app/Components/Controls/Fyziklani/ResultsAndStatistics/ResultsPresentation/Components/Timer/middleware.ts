export interface DeltaTimes {
    toStart: number;
    toEnd: number;
}

export const getCurrentDelta = (toStart: number, toEnd: number, inserted: Date): DeltaTimes => {
    if(!inserted){
        return {
            toEnd: 0,
            toStart: 0,
        };
    }
    const now = new Date();
    const delta = now.getTime() - inserted.getTime();
    return {
        toEnd: toEnd - delta,
        toStart: toStart - delta,
    };
};
