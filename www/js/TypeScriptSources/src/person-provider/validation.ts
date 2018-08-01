export const isMail = (value: string): boolean => {
    return /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(value);
};
export const required = (value): boolean => {
    return (value ? true : false);
};

export const getAccessKey = (person: string, property: string): string => {
    return person + '.' + property;
};
