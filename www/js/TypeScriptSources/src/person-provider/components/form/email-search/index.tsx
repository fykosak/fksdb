import * as React from 'react';
import Button from './button';
import Input from './input';

export default class Index extends React.Component<{}, {}> {
    public render() {
        const accessKey = '@@person-provider/email-search'
        return <div>

            <label>Email</label>
            <div className={'input-group'}>
                <Input/>
                <div className="input-group-btn">
                    <Button accessKey={accessKey}/>
                </div>
            </div>
        </div>;
    }
}
