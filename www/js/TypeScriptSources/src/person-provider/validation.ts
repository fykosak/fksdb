export const isMail = (value: string): string => {
    return /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/.test(value) ? undefined : 'is not a valid Mail';
};
export const required = (value): string => {
    return (value ? undefined : 'Required');
};

export const getAccessKey = (person: string, property: string): string => {
    return person + '.' + property;
};
