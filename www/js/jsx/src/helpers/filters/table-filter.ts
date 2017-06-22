import {
    Filter,
} from './filters';

const defaultFilter = new Filter({room: null, category: null, name: ''});

export const createFilter = (filterID: number = 0,
                             autoSwitch: boolean = false,
                             filterOptions: any = null,
                             userFilter: Filter = null): Filter => {

    if (userFilter) {
        return userFilter;
    }
    if (!autoSwitch) {
        return defaultFilter;
    }

    switch (filterID) {
        case 0:
        default:
            return defaultFilter;
        case 1:
            return getRoomFilter(filterOptions);
        case 2:
            return getCategoryFilter(filterOptions);
    }
};

const getRoomFilter = (filterOptions): Filter => {
    if (filterOptions.room) {
        return new Filter({room: filterOptions.room, category: null, name: ''});
    }
    return defaultFilter;
};

const getCategoryFilter = (filterOptions): Filter => {
    if (filterOptions.category) {
        return new Filter({room: null, category: filterOptions.category, name: ''});
    }
    return defaultFilter;
};
