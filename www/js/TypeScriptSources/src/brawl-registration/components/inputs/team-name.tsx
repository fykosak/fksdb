import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';

export default class TeamName extends React.Component<WrappedFieldProps, {}> {
    public render() {
        const {input} = this.props;
        return <div>
            <label>Team name</label>
            <input {...input} type="text"/>
        </div>;
    }
}
