import * as React from 'react';

import Place from './place';

import { IRoom } from '../interfaces';

interface IProps {
    info: IRoom;
}

export default  class Room extends React.Component<IProps, {}> {

    public render() {
        const { info } = this.props;
        const { name, x: maxX, y: maxY } = info;
        const rows = [];
        for (let y = 0; y < maxY; y++) {
            const row = [];
            for (let x = 0; x < maxX; x++) {
                row.push(<Place
                    key={x}
                    x={x}
                    y={y}
                    room={name}
                />);
            }
            rows.push(<tr key={y}>{row}</tr>);
        }

        return (
            <div className="row">
                <h3 className="col-lg-12">{name}</h3>
                <table className="table">
                    {rows}
                </table>

            </div>
        );
    }
}
