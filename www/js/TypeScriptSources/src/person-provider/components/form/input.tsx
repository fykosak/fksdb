import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import Lang from '../../../lang/components/lang';

export default class Input extends React.Component<WrappedFieldProps & {}, {}> {
    public render() {
        const {input, meta: {valid, touched}} = this.props;
        return <>
            <label><Lang text={'email'}/></label>
            <input
                {...input}
                type="email"
                required={true}
                className={'form-control' + ((touched && valid) ? ' is-invalid' : '')}
                placeholder="you-mail@example.com"
            />
        </>;
    }
}
