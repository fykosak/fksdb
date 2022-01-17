import * as React from 'react';
import { Room } from '../../../helpers/interfaces';
import RoomComponent from './Room';

interface OwnProps {
    rooms: Room[];
}

export default class Rooms extends React.Component<OwnProps> {

    public render() {
        const {rooms} = this.props;
        return (<>
            {rooms.map((room, index) => {
                return (<RoomComponent key={index} info={room}/>);
            })}
        </>);
    }
}
