import { availableLanguage, LangMap } from '@translator/translator';

export interface SubmitModel {
    submitId: number | null;
    name: LangMap<availableLanguage, string>;
    deadline: string | null;
    taskId: number;
    isQuiz: boolean;
    disabled: boolean;
}
