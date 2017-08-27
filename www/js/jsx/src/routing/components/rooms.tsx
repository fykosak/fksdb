import * as React from 'react';

import Room from './room';

interface IProps {
    rooms: any[];
}

export default class Rooms extends React.Component<IProps, {}> {

    public render() {
        const { rooms } = this.props;
        return (<div>
            {rooms.map((room, index) => {
                const { x, y, name } = room;
                return (<Room key={index} x={x} y={y} name={name}/>);
            })}
        </div>);
    }
}
