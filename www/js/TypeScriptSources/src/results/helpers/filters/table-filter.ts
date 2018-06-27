import { Filter } from './filters';

const defaultFilter = new Filter({roomId: null, category: null, name: 'All'});

export const createFilter = (filterId: number = 0,
                             autoSwitch: boolean = false,
                             filterOptions: { roomId?: number; category?: string } = null,
                             userFilter: Filter = null): Filter => {

    if (userFilter) {
        return userFilter;
    }
    if (!autoSwitch) {
        return defaultFilter;
    }

    switch (filterId) {
        case 0:
        default:
            return defaultFilter;
        case 1:
            return getRoomFilter(filterOptions);
        case 2:
            return getCategoryFilter(filterOptions);
    }
};

const getRoomFilter = (filterOptions: { roomId?: number; category?: string }): Filter => {
    if (filterOptions.roomId) {
        return new Filter({roomId: filterOptions.roomId, category: null, name: 'Current room'});
    }
    return defaultFilter;
};

const getCategoryFilter = (filterOptions: { roomId?: number; category?: string }): Filter => {
    if (filterOptions.category) {
        return new Filter({roomId: null, category: filterOptions.category, name: 'Category ' + filterOptions.category});
    }
    return defaultFilter;
};
