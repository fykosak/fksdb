import { lang } from '@i18n/i18n';
import { Room } from '../../../../helpers/interfaces';
import { Filter } from './filter';

export const createFilters = (rooms: Room[] = [], categories: string[] = [], includeAll: boolean = true): Filter[] => {
    const roomFilters = rooms.map((room: Room) => {
        return new Filter({roomId: room.roomId, category: null, name: 'Room ' + room.name});
    });

    const categoriesFilters = categories.map((category: string) => {
        return new Filter({roomId: null, category, name: lang.getText('Category') + ' ' + category});
    });
    const filters = [];
    if (includeAll) {
        filters.push(new Filter({roomId: null, category: null, name: lang.getText('All')}));
    }
    return filters.concat(roomFilters).concat(categoriesFilters);
};
