export const getCurrentDelta = ({ toStart, toEnd }, inserted) => {
    const now = new Date();
    const delta = now.getTime() - inserted.getTime();
    return {
        currentToEnd: toEnd - delta,
        currentToStart: toStart - delta,
    };
};
