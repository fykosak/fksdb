import * as React from 'react';

export default abstract class ChartComponent<Props, State> extends React.Component<Props, State> {

    protected readonly size = {
        height: 600,
        width: 900,
    };
    protected readonly margin = {
        bottom: 30,
        left: 40,
        right: 40,
        top: 30,
    };

    protected getViewBox(): string {
        return '0 0 ' + this.size.width + ' ' + this.size.height;
    }

    protected getInnerXSize(): [number, number] {
        return [this.margin.left, this.size.width - this.margin.right];
    }

    protected getInnerYSize(): [number, number] {
        return [this.size.height - this.margin.top, this.margin.bottom];
    }

    protected transformXAxis(): string {
        return 'translate(0,' + (this.size.height - this.margin.bottom) + ')';
    }

    protected transformYAxis(): string {
        return 'translate(' + this.margin.left + ',0)';
    }

    public static readonly size = {
        height: 600,
        width: 900,
    };
    public static readonly margin = {
        bottom: 30,
        left: 40,
        right: 40,
        top: 30,
    };

    public static getViewBox(): string {
        return '0 0 ' + this.size.width + ' ' + this.size.height;
    }

    public static getInnerXSize(): [number, number] {
        return [this.margin.left, this.size.width - this.margin.right];
    }

    public static getInnerYSize(): [number, number] {
        return [this.size.height - this.margin.top, this.margin.bottom];
    }

    public static transformXAxis(): string {
        return 'translate(0,' + (this.size.height - this.margin.bottom) + ')';
    }

    public static transformYAxis(): string {
        return 'translate(' + this.margin.left + ',0)';
    }
}
