import { Filter } from './filter';
import { translator } from '@translator/Translator';

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
