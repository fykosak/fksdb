import * as React from 'react';
import { IRoom } from '../../helpers/interfaces';
import Room from './room';

interface IProps {
    rooms: IRoom[];
}

export default class Rooms extends React.Component<IProps, {}> {

    public render() {
        const { rooms } = this.props;
        return (<div>
            {rooms.map((room, index) => {
                return (<Room key={index} info={room}/>);
            })}
        </div>);
    }
}
