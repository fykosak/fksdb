import { IRoom } from '../../../../helpers/interfaces';
import { Filter } from './filter';

export const createFilters = (rooms: IRoom[] = [], categories: string[] = [], includeAll: boolean = true): Filter[] => {
    const roomFilters = rooms.map((room: IRoom) => {
        return new Filter({roomId: room.roomId, category: null, name: 'Room ' + room.name});
    });

    const categoriesFilters = categories.map((category: string) => {
        return new Filter({roomId: null, category, name: 'Category ' + category});
    });
    const filters = [];
    if (includeAll) {
        filters.push(new Filter({roomId: null, category: null, name: 'All'}));
    }
    return filters.concat(roomFilters).concat(categoriesFilters);
};
