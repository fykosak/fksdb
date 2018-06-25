import * as React from 'react';
import { connect } from 'react-redux';
import { IPersonSelector } from '../../brawl-registration/middleware/price';
import { IStore } from '../interfaces';
import { ISectionDefinition } from './fields/interfaces';
import Form from './form/';
import PersonForm from './form/person-form';

interface IProps {
    personSelector: IPersonSelector;
    html?: string;
}

interface IState {
    isServed?: boolean;
    form?: {
        [key: string]: ISectionDefinition;
    };
}

class PersonProvider extends React.Component<IProps & IState, {}> {

    public render() {
        const {personSelector, form} = this.props;

        if (this.props.isServed) {
            // if (children) {

            return <div>
                <PersonForm form={form} personSelector={personSelector}/>
            </div>;
            // } else {
            // return <div dangerouslySetInnerHTML={{__html: this.props.html}}/>;
            // }

        } else {
            return <Form accessKey={this.props.personSelector.accessKey}/>;
        }
    }
}

const mapDispatchToProps = (): IState => {
    return {};
};

const mapStateToProps = (state: IStore, ownProps: IProps): IState => {
    const accessKey = ownProps.personSelector.accessKey;
    if (state.provider.hasOwnProperty(accessKey)) {
        return {
            form: state.provider[accessKey].form,
            isServed: state.provider[accessKey].isServed,
        };
    }
    return {};
};

export default connect(mapStateToProps, mapDispatchToProps)(PersonProvider);
