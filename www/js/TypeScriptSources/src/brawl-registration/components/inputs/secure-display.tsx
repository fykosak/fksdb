import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { WrappedFieldProps } from 'redux-form';
import Lang from '../../../lang/components/lang';
import { clearProviderProviderProperty } from '../../../person-provider/actions';
import { IStore } from '../../reducers';
import { IInputProps } from './input';

interface IState {
    onClearValue?: () => void;
}

class SecureDisplay extends React.Component<WrappedFieldProps & IInputProps & IState, {}> {

    public render() {
        const {onClearValue, JSXLabel} = this.props;

        return <div className="form-group">
            <label className="text-success">{JSXLabel}<span className="fa fa-check ml-1"/></label>
            <small className="text-muted form-text"><Lang
                text={'Tento udaj už v systéme máme uložený, ak ho chcete zmeniť kliknite na tlačítko upraviť'}/></small>
            <button className="btn btn-warning btn-sm" onClick={(event) => {
                event.preventDefault();
                onClearValue();
            }}>
                <span className="fa fa-edit mr-1"/><Lang text={'Upraviť'}/>
            </button>
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<IStore>, ownProps: IInputProps & WrappedFieldProps): IState => {
    return {
        onClearValue: () => dispatch(clearProviderProviderProperty(ownProps.input.name)),
    };
};

const mapStateToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(SecureDisplay);
