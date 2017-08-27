export const getCurrentDelta = ({ toStart, toEnd }, inserted) => {
    const now = new Date();
    const delta = now.getTime() - inserted.getTime();
    toEnd -= delta;
    toStart -= delta;
    return {
        currentToEnd: toEnd,
        currentToStart: toStart,
    };
};
