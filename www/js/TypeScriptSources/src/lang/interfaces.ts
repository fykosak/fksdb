export interface ILanguageDefinition {
    lang: string;
    data: {
        [key: string]: string;
    };
}

export interface ILanguageResponseData {
    [key: string]: string;
}
