import {IFilter} from './filters';
export const createFilter = (filterID: number = 0, autoSwitch: boolean = false, filterOptions: any = null, userFilter: IFilter = null): IFilter => {
    const defaultFilter = {room: null, category: null, name: ""};
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
            if (filterOptions.room) {
                return {room: filterOptions.room, category: null, name: ""};
            }
            return defaultFilter;
        case 2:
            if (filterOptions.category) {
                return {room: null, category: filterOptions.category, name: ""};
            }
            return defaultFilter;
    }
};