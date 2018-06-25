import { IPersonSelector } from '../../../brawl-registration/middleware/price';

export interface ISectionDefinition {
    name: string;
    fields: {
        [name: string]: IInputDefinition;
    };
}

export interface IInputDefinition<D = string> {
    readonly: boolean;
    secure: boolean;
    description?: string | JSX.Element;
    required: boolean;
    filled: boolean;
    value: D;
    userChange?: boolean;
}

export interface ISectionProps {
    personSelector: IPersonSelector;
    sectionDef: ISectionDefinition;
}
