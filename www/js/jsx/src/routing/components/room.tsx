import * as React from 'react';

import Place from './place';

interface IProps {
    name: string;
    x: number;
    y: number;
}

export default  class Room extends React.Component<IProps, {}> {

    public render() {
        const { name, x: maxX, y: maxY } = this.props;

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
