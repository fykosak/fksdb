export const ACTION_CLEAR_PROVIDER_PROPERTY = '@@person-provider/CLEAR_PROVIDER_PROPERTY';

export const clearProviderProviderProperty = (selector: string, property: string) => {
    return {
        selector,
        property,
        type: ACTION_CLEAR_PROVIDER_PROPERTY,
    };
};
