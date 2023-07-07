// @es-ignore
// eslint-disable-next-line @typescript-eslint/no-namespace
export namespace ChartComponent {

    export const size = {
        height: 600,
        width: 900,
    };
    export const margin = {
        bottom: 30,
        left: 40,
        right: 40,
        top: 30,
    };

    export function getViewBox(): string {
        return '0 0 ' + this.size.width + ' ' + this.size.height;
    }

    export function getInnerXSize(): [number, number] {
        return [this.margin.left, this.size.width - this.margin.right];
    }

    export function getInnerYSize(): [number, number] {
        return [this.size.height - this.margin.top, this.margin.bottom];
    }

    export function transformXAxis(): string {
        return 'translate(0,' + (this.size.height - this.margin.bottom) + ')';
    }

    export function transformYAxis(): string {
        return 'translate(' + this.margin.left + ',0)';
    }
}
