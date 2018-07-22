import * as React from 'react';

import Room from './room';

import { IRoom } from '../../shared/interfaces';

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
