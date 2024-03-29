import { Translator } from '@translator/translator';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/team-model';

export class Filter {
    public name: string;
    public category: string | null;

    constructor({category, name}: { category: string | null, name: string }) {
        this.category = category;
        this.name = name;
    }

    public match(team: TeamModel): boolean {
        const {category} = team;
        return !(this.category && this.category !== category);
    }

    public same(filter: Filter | null): boolean {
        return (filter) && (filter.category === this.category) && (filter.name === this.name);
    }

    public getHeadline(): string {
        return this.name;
    }
}

export const createFilters = (categories: string[] = [], translator: Translator): Filter[] => {
    return categories.map((category: string) => {
        return new Filter({category, name: translator.getText('Category') + ' ' + category});
    });
};
