import * as React from 'react';
import MultiSelect from './multi-select';
import SingleSelect from './single-select';

interface IProps {
    mode: string;
}

export default class Select extends React.Component<IProps, {}> {

    public render() {
        const {mode} = this.props;
        if (mode === 'presentation') {
            return <MultiSelect/>;
        }
        return <SingleSelect/>;
    }
}
