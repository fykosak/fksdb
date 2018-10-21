import * as React from 'react';
import { IData } from './index';
import Row from './row';

interface IProps {
    data: IData;
}

export default class Schedule extends React.Component<IProps, {}> {

    public render() {
        const {data} = this.props;
        const rows = [];
        for (const blockName in data) {
            if (data.hasOwnProperty(blockName)) {
                const blockData = data[blockName];
                rows.push(<Row key={blockName} blockData={blockData} blockName={blockName}/>);
            }
        }

        return (
            <div className={'schedule-field-container'}>
                {rows}
            </div>
        );
    }
}
