import * as React from 'react';
import { IEventAccommodation } from '../../middleware/interfaces';
import SingleRow from '../rows/single-row';

interface IProps {
    accommodationDef?: IEventAccommodation[];
}

export default class Single extends React.Component<IProps, {}> {

    public render() {
        const {accommodationDef} = this.props;
        if (accommodationDef.length !== 1 && accommodationDef.hasOwnProperty(0)) {
            throw new Error('Wrong type of accommodation');
        }
        return <SingleRow accommodationItem={accommodationDef[0]}/>;
    }
}
