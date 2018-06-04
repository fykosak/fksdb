import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import Lang from '../../lang/components/lang';

interface IProps {
    accessKey: string;
}

export default class Password extends React.Component<IProps & WrappedFieldProps, {}> {

    public render() {
        const {input, meta: {valid, touched}} = this.props;
        return <>
            <label><Lang text={'password'}/></label>
            <input
                {...input}
                required={true}
                className={'form-control' + ((touched && valid) ? ' is-invalid' : '')}
                type="password"
            />
        </>;
    }

}
