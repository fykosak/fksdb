import * as React from 'react';
import MultiSelect from './multi-select';
import SingleSelect from './single-select';

interface IProps {
    mode: string;
}

export default class Select extends React.Component<IProps, {}> {

    public render() {
        if (this.props.mode === 'presentation') {
            return <MultiSelect/>;
        }
        return <SingleSelect/>;
    }
}
