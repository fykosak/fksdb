import * as React from 'react';

export default class Sponsors extends React.Component<{}, {}> {

    public render() {
        return (
            <div className={'align-items-center container-fluid mt-3 row sponsors justify-content-center'}>
                <div className={'col-3'}>
                    <img src="//fyziklani.cz/_media/sponsors/matfyz_uni.png" alt=""/>
                </div>
                <div className={'col-3'}>
                    <img src="//fyziklani.cz/_media/sponsors/fykos_uni.png?w=400&amp;tok=3382a1" alt=""/>
                </div>
                <div className={'col-3'}>
                    <img src="//fyziklani.cz/_media/sponsors/on_uni.png?w=400&amp;tok=b32a0d" alt=""/>
                </div>
                <div className={'col-3'}>
                    <img src="//fyziklani.cz/_media/sponsors/mindok_uni.png?w=400&amp;tok=034fed" alt=""/>
                </div>
                <div className={'col-3'}>
                    <img src="//fyziklani.cz/_media/sponsors/msmt_cs.png?w=400&amp;tok=a8cdbe" alt=""/>
                </div>
                <div className={'col-3'}>
                    <img src="//fyziklani.cz/_media/sponsors/cscasp_uni.jpg?w=400&amp;tok=ddaffc" alt=""/>
                </div>
            </div>
        );
    }
}
