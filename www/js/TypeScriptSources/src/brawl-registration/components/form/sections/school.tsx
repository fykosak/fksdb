import * as React from 'react';
import Lang from '../../../../lang/components/lang';
import SchoolId from '../../../../person-provider/components/fields/person-history/school-id';
import StudyYear from '../../../../person-provider/components/fields/person-history/study-year';
import { getFieldName } from '../../../middleware/person';
import { IPersonSelector } from '../../../middleware/price';

export default class SchoolSection extends React.Component<IPersonSelector, {}> {
    public render() {
        const {type, index} = this.props;
        const accessKey = getFieldName(type, index);
        return <div>
            <h3><Lang text={'School'}/></h3>
            <SchoolId accessKey={accessKey}/>
            <StudyYear accessKey={accessKey}/>
        </div>;
    }
}
