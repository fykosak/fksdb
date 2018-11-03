import * as React from 'react';
import {
    connect,
    Dispatch,
} from 'react-redux';
import { lang } from '../../../i18n/i18n';
import { changeData } from '../../../input-connector/actions';
import PriceDisplay from '../../../shared/components/displays/price';
import { IFyziklaniScheduleStore } from '../reducers/';
import { IChooserParallel } from './index';

interface IProps {
    item: IChooserParallel;
    blockName: string;
}

interface IState {
    value?: number;

    setSchedule?(id: number): void;
}

class ChooserItem extends React.Component<IProps & IState, {}> {

    public render() {
        const {item, value} = this.props;
        const langKey = lang.getCurrentLocale();
        if (!item.hasOwnProperty(langKey)) {
            return null;
        }
        const active = item.id === value;
        const localizedData = item[langKey];
        return (
            <div className={'chooser-container row ' + (active ? 'active' : '')} onClick={() => {
                this.props.setSchedule(active ? null : item.id);
            }}>
                <div className={'col-2 chooser-check-container'}>
                    <span className={active ? 'fa fa-check-square-o' : 'fa fa-square-o'}/>
                </div>
                <div className={'col-10'}>
                    <span className={'h5'}>{localizedData.name}</span>
                    <div className={'row'}>
                        <div className={'align-items-center col-2 d-flex'}>
                            <span className={'fa fa-question-circle-o mr-2'}/>
                        </div>
                        <div className={'col-10'}>
                            {localizedData.description}
                        </div>
                    </div>
                    <div className={'small row'}>
                        <div className={'align-items-center col-2 d-flex'}>
                            <span className={'fa fa-dollar mr-2'}/>
                        </div>
                        <div className={'col-10'}>
                            <PriceDisplay price={item.price}/>
                        </div>

                    </div>
                </div>
            </div>
        );
    }
}

const mapStateToProps = (store: IFyziklaniScheduleStore, ownProps: IProps): IState => {
    const {blockName} = ownProps;
    return {
        value: store.inputConnector.data.hasOwnProperty(blockName) ? store.inputConnector.data[blockName] : null,
    };
};

const mapDispatchToProps = (dispatch: Dispatch<IFyziklaniScheduleStore>, ownProps: IProps): IState => {
    return {
        setSchedule: (id: number) => dispatch(changeData(ownProps.blockName, id)),
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(ChooserItem);
