import * as React from 'react';
import { connect } from 'react-redux';
import { Dispatch } from 'redux';
import { WrappedFieldProps } from 'redux-form';
import { clearProviderProviderProperty } from '../../../person-provider/actions';
import Lang from '../../../lang/components/lang';

interface IProps {
    modifiable: boolean;
}

interface IState {
    onClearValue?: () => void;
}

class SecureDisplay extends React.Component<WrappedFieldProps & IState & IProps, {}> {

    public render() {
        const {modifiable, onClearValue} = this.props;
        return <div className="row">
            <div className={modifiable ? 'col-8' : 'col-12'}>
                <span className="text-success">
                    <Lang text={'Tento udaj už v systéme máme uložený, ak ho chcete zmeniť kliknite na tlačítko upraviť'}/>
                </span>
            </div>
            {modifiable && (<div className="col-4">
                <button className="btn btn-warning" onClick={(event) => {
                    event.preventDefault();
                    onClearValue();
                }}>
                    <Lang text={'Opraviť hodnotu'}/>
                </button>
            </div>)}
        </div>;
    }
}

const mapDispatchToProps = (dispatch: Dispatch<any>, ownProps: any): IState => {
    return {
        onClearValue: () => dispatch(clearProviderProviderProperty(ownProps.input.name)),
    };
};

const mapStateToProps = (): IState => {
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(SecureDisplay);
