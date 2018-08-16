import { Filter } from '../../../../../results/helpers/filters/filters';
import { IRoom } from '../../../../../shared/interfaces';

export const createFilters = (rooms: IRoom[] = [], categories: string[] = []): Filter[] => {
    const roomFilters = rooms.map((room: IRoom) => {
        return new Filter({roomId: room.roomId, category: null, name: 'Room ' + room.name});
    });

    const categoriesFilters = categories.map((category: string) => {
        return new Filter({roomId: null, category, name: 'Category ' + category});
    });

    return [new Filter({roomId: null, category: null, name: 'All'})].concat(roomFilters).concat(categoriesFilters);

};
