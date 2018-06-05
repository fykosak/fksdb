import * as React from 'react';
import { WrappedFieldProps } from 'redux-form';
import Lang from '../../../lang/components/lang';
import { IInputProps } from './input';

interface IProps {
    removeProviderValue: () => void;
}

export default class SecureDisplay extends React.Component<WrappedFieldProps & IInputProps & IProps, {}> {

    public render() {
        const {removeProviderValue, JSXLabel} = this.props;

        return <div className="form-group">
            <label className="text-success">{JSXLabel}<span className="fa fa-check ml-1"/></label>
            <small className="text-muted form-text"><Lang
                text={'Tento udaj už v systéme máme uložený, ak ho chcete zmeniť kliknite na tlačítko upraviť'}/></small>
            <button className="btn btn-warning btn-sm" onClick={(event) => {
                event.preventDefault();
                removeProviderValue();
            }}>
                <span className="fa fa-edit mr-1"/><Lang text={'Upraviť'}/>
            </button>
        </div>;
    }
}
