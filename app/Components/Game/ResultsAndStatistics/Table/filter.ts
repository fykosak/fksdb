import { translator } from '@translator/translator';
import { TeamModel } from 'FKSDB/Models/ORM/Models/Fyziklani/TeamModel';

export class Filter {
    public name: string;
    public category: string;
    public roomId: number;

    constructor({roomId, category, name}) {
        this.category = category;
        this.roomId = roomId;
        this.name = name;
    }

    public match(team: TeamModel): boolean {
        const {category} = team;
        return !(this.category && this.category !== category);
    }

    public same(filter: Filter | null): boolean {
        return (filter) && (filter.roomId === this.roomId) && (filter.category === this.category) && (filter.name === this.name);
    }

    public getHeadline(): string {
        return this.name;
    }
}

export const createFilters = (categories: string[] = []): Filter[] => {
    return categories.map((category: string) => {
        return new Filter({roomId: null, category, name: translator.getText('Category') + ' ' + category});
    });
};
