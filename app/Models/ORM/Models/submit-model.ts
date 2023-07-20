import { LangMap } from '@translator/translator';

export interface SubmitModel {
    submitId: number | null;
    name: LangMap<string, 'cs' | 'en'>;
    deadline: string | null;
    taskId: number;
    isQuiz: boolean;
    disabled: boolean;
}
