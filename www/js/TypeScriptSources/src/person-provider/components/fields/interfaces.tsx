import { IPersonSelector } from '../../../brawl-registration/middleware/price';

export interface ISectionDefinition {
    name: string;
    fields: {
        [name: string]: IInputDefinition;
    };
}

export interface IInputDefinition {
    readonly: boolean;
    secure: boolean;
    description?: string;
    required: boolean;
}

export interface ISectionProps {
    personSelector: IPersonSelector;
    sectionDef: ISectionDefinition;
}
