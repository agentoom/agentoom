const chartNumber = (value) => Number.isFinite(Number(value)) ? Number(value) : 0;

const parseFormat = (attr) => {
    if (!attr) return null;
    try {
        const parsed = JSON.parse(attr);
        return parsed && typeof parsed === 'object' ? parsed : null;
    } catch {
        return null;
    }
};

const isNumberFormat = (options) => Boolean(options && ('style' in options || 'notation' in options || 'unit' in options));

const formatters = new Map();

const formatValue = (value, options) => {
    if (value === null || value === undefined || value === '') return '';
    if (!options) return String(value);
    const kind = isNumberFormat(options) ? 'number' : 'date';
    const key = kind + JSON.stringify(options);
    if (!formatters.has(key)) {
        formatters.set(key, kind === 'number' ? new Intl.NumberFormat(undefined, options) : new Intl.DateTimeFormat(undefined, options));
    }
    try {
        return formatters.get(key).format(kind === 'number' ? Number(value) : new Date(value));
    } catch {
        return String(value);
    }
};

class AgentoomChart extends HTMLElement {
    static get observedAttributes() { return ['value']; }

    connectedCallback() {
        this._initialized = true;
        this.render();
        this._observer = new ResizeObserver(() => {
            cancelAnimationFrame(this._frame);
            this._frame = requestAnimationFrame(() => this.render());
        });
        this._observer.observe(this);
    }

    disconnectedCallback() {
        this._initialized = false;
        this._observer?.disconnect();
        cancelAnimationFrame(this._frame);
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'value' && this._initialized && oldValue !== newValue) this.render();
    }

    render() {
        let data;
        try { data = JSON.parse(this.getAttribute('value') || '[]'); } catch { data = []; }
        if (!Array.isArray(data) || data.length === 0) return;

        let svg = this.querySelector('svg');
        let gutter = 20;
        if (!svg) {
            const svgTemplate = this.querySelector('template[name="svg"]');
            gutter = Number(svgTemplate?.getAttribute('gutter') || 20);
            svg = svgTemplate?.content.firstElementChild?.cloneNode(true);
            if (svg) svgTemplate.replaceWith(svg);
        }
        if (!svg) return;

        const width = Math.max(this.clientWidth || 640, 240);
        const height = Math.max(this.clientHeight || 220, 160);
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svg.setAttribute('preserveAspectRatio', 'none');
        svg.querySelectorAll('[data-agentoom-rendered]').forEach((node) => node.remove());

        const series = [...svg.querySelectorAll('template[name="line"], template[name="area"]')];
        const fields = series.map((template) => template.getAttribute('field') || 'value');
        const values = fields.flatMap((field) => data.map((row) => chartNumber(row?.[field])));
        const min = Math.min(0, ...values);
        const max = Math.max(1, ...values);
        const x = (index) => gutter + (index / Math.max(data.length - 1, 1)) * (width - gutter * 2);
        const y = (value) => height - gutter - ((value - min) / (max - min)) * (height - gutter * 2);
        const ns = 'http://www.w3.org/2000/svg';
        const color = (template) => {
            const classes = template.getAttribute('class') || '';
            if (classes.includes('red')) return '#ef4444';
            if (classes.includes('emerald')) return '#10b981';
            if (classes.includes('purple')) return '#a855f7';
            return '#3b82f6';
        };

        series.forEach((template) => {
            const field = template.getAttribute('field') || 'value';
            const points = data.map((row, index) => `${x(index)},${y(chartNumber(row?.[field]))}`);
            if (template.getAttribute('name') === 'area') {
                const area = document.createElementNS(ns, 'path');
                area.setAttribute('d', `M ${points.join(' L ')} L ${x(data.length - 1)},${height - gutter} L ${x(0)},${height - gutter} Z`);
                area.setAttribute('fill', color(template));
                area.setAttribute('fill-opacity', '.15');
                area.setAttribute('stroke', 'none');
                area.setAttribute('data-agentoom-rendered', 'true');
                svg.append(area);
            }
            const path = document.createElementNS(ns, 'path');
            path.setAttribute('d', `M ${points.join(' L ')}`);
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke', color(template));
            path.setAttribute('stroke-width', '2');
            path.setAttribute('data-agentoom-rendered', 'true');
            svg.append(path);
        });

        svg.querySelectorAll('template[name="axis"]').forEach((axis) => {
            const axisName = axis.getAttribute('axis') || 'x';
            const axisFormat = parseFormat(axis.getAttribute('format'));
            const lineTemplate = axis.content.querySelector('template[name="axis-line"]');
            const tickTemplate = axis.content.querySelector('template[name="tick-label"]');

            const line = lineTemplate
                ? lineTemplate.content.firstElementChild?.cloneNode(true)
                : document.createElementNS(ns, 'line');
            if (line) {
                if (axisName === 'x') {
                    line.setAttribute('x1', gutter); line.setAttribute('x2', width - gutter);
                    line.setAttribute('y1', height - gutter); line.setAttribute('y2', height - gutter);
                } else {
                    const lineX = axis.getAttribute('position') === 'right' ? width - gutter : gutter;
                    line.setAttribute('x1', lineX); line.setAttribute('x2', lineX);
                    line.setAttribute('y1', gutter); line.setAttribute('y2', height - gutter);
                }
                line.setAttribute('data-agentoom-rendered', 'true');
                svg.append(line);
            }

            if (!tickTemplate) return;

            if (axisName === 'x') {
                const step = Math.max(1, Math.ceil(data.length / 6));
                for (let index = 0; index < data.length; index += step) {
                    const group = tickTemplate.content.firstElementChild?.cloneNode(true);
                    const text = group?.querySelector('text');
                    if (!text) continue;
                    text.setAttribute('x', x(index));
                    text.setAttribute('y', height - gutter);
                    text.textContent = formatValue(data[index]?.[axis.getAttribute('field') || 'date'], axisFormat);
                    group.setAttribute('data-agentoom-rendered', 'true');
                    svg.append(group);
                }
            } else {
                for (let index = 0; index <= 4; index++) {
                    const value = min + ((max - min) / 4) * index;
                    const group = tickTemplate.content.firstElementChild?.cloneNode(true);
                    const text = group?.querySelector('text');
                    if (!text) continue;
                    text.setAttribute('x', axis.getAttribute('position') === 'right' ? width - gutter : gutter);
                    text.setAttribute('y', y(value));
                    text.textContent = formatValue(value, axisFormat);
                    group.setAttribute('data-agentoom-rendered', 'true');
                    svg.append(group);
                }
            }
        });

        this.buildTooltip();
        this.buildCursor(svg, ns, height, gutter);
        this._data = data;
        this._geom = { x, y, width, height, gutter };
        this.bindPointerHandlers();
    }

    buildTooltip() {
        if (this._tooltip) return;
        const template = this.querySelector('template[name="tooltip"]');
        if (!template) return;
        const content = template.content.firstElementChild?.cloneNode(true);
        if (!content) return;
        content.classList.add('absolute', 'z-10');
        content.style.display = 'none';
        this.append(content);
        this._tooltip = content;
        this._slots = [...content.querySelectorAll('slot[field]')].map((slot) => {
            const span = document.createElement('span');
            const meta = {
                field: slot.getAttribute('field'),
                format: parseFormat(slot.getAttribute('format')),
            };
            slot.replaceWith(span);
            return { element: span, ...meta };
        });
    }

    buildCursor(svg, ns, height, gutter) {
        this._cursor = null;
        const template = this.querySelector('template[name="cursor"]');
        if (!template) return;
        const cursor = template.content.firstElementChild?.cloneNode(true);
        if (!cursor) return;
        cursor.setAttribute('x1', 0); cursor.setAttribute('x2', 0);
        cursor.setAttribute('y1', gutter); cursor.setAttribute('y2', height - gutter);
        cursor.setAttribute('visibility', 'hidden');
        cursor.setAttribute('data-agentoom-cursor', 'true');
        cursor.setAttribute('data-agentoom-rendered', 'true');
        svg.append(cursor);
        this._cursor = cursor;
    }

    bindPointerHandlers() {
        if (this._bound) return;
        this._bound = true;
        this.addEventListener('mousemove', (event) => {
            if (!this._data || !this._geom) return;
            const { x, width, height, gutter } = this._geom;
            const rect = this.getBoundingClientRect();
            const index = Math.max(0, Math.min(this._data.length - 1, Math.round(((event.clientX - rect.left - gutter) / Math.max(rect.width - gutter * 2, 1)) * (this._data.length - 1))));
            const row = this._data[index] || {};

            if (this._cursor) {
                const cursorX = x(index);
                this._cursor.setAttribute('x1', cursorX);
                this._cursor.setAttribute('x2', cursorX);
                this._cursor.setAttribute('visibility', 'visible');
            }

            this._slots?.forEach((slot) => {
                slot.element.textContent = formatValue(row[slot.field], slot.format);
            });

            if (this._tooltip) {
                this._tooltip.style.display = '';
                this._tooltip.style.opacity = '1';
                const left = Math.min(Math.max(event.clientX - rect.left + 14, 0), Math.max(width - this._tooltip.offsetWidth - 4, 0));
                const top = event.clientY - rect.top - this._tooltip.offsetHeight - 8;
                this._tooltip.style.left = `${left}px`;
                this._tooltip.style.top = `${Math.max(top, 0)}px`;
            }
        });
        this.addEventListener('mouseleave', () => {
            if (this._tooltip) {
                this._tooltip.style.display = 'none';
                this._tooltip.style.opacity = '0';
            }
            if (this._cursor) this._cursor.setAttribute('visibility', 'hidden');
        });
    }
}

if (!customElements.get('ui-chart')) customElements.define('ui-chart', AgentoomChart);
