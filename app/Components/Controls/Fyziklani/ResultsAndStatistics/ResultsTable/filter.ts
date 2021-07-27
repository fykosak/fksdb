import { translator } from '@translator/translator';
import { ModelFyziklaniTeam } from 'FKSDB/Models/ORM/Models/Fyziklani/modelFyziklaniTeam';

export class Filter {
    public name: string;
    public category: string;
    public roomId: number;

    constructor({roomId, category, name}) {
        this.category = category;
        this.roomId = roomId;
        this.name = name;
    }

    public match(team: ModelFyziklaniTeam): boolean {
        const {category} = team;
        if (this.category && this.category !== category) {
            return false;
        }
        // return !(this.roomId && this.roomId !== roomId);
        return true;
    }

    public same(filter: Filter): boolean {
        if (!filter) {
            return false;
        }
        return (filter.roomId === this.roomId) && (filter.category === this.category) && (filter.name === this.name);
    }

    public getHeadline(): string {
        return this.name;
    }
}

export const createFilters = (categories: string[] = [], includeAll: boolean = true): Filter[] => {

    const categoriesFilters = categories.map((category: string) => {
        return new Filter({roomId: null, category, name: translator.getText('Category') + ' ' + category});
    });
    const filters = [];
    if (includeAll) {
        filters.push(new Filter({roomId: null, category: null, name: translator.getText('All')}));
    }
    return filters.concat(categoriesFilters);
};
