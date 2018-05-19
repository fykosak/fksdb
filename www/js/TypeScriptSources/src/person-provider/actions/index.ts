export const ACTION_CLEAR_PROVIDER_PROPERTY = 'ACTION_CLEAR_PROVIDER_PROPERTY';

export const clearProviderProviderProperty = (selector: string) => {
    return {
        selector,
        type: ACTION_CLEAR_PROVIDER_PROPERTY,
    };
};
