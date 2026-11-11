import { calculateDoorGeometry, fitDoorGeometry } from './sliding-door-geometry.js';
import { frameVisual, infillVisual } from './sliding-door-visuals.js';

const SVG_NAMESPACE = 'http://www.w3.org/2000/svg';

/**
 * Rendering proportions and stroke widths are illustrative, not fabrication sizes.
 * The configuration supplies geometry inputs, frameColor and optional infillIdsByDoor.
 */
export function createPreviewScene(configuration, metadata, viewport) {
    const fitted = fitDoorGeometry(calculateDoorGeometry(configuration), { ...viewport, padding: 48 });

    return {
        ...fitted,
        frameFill: frameVisual(metadata, configuration.frameColor).fill,
        doors: fitted.doors.map((door) => ({
            ...door,
            frameWidth: Math.min(6, door.width / 12, door.height / 12),
            panels: door.panels.map((panel) => ({
                ...panel,
                fill: infillVisual(metadata, configuration.infillIdsByDoor?.[door.index]?.[panel.index]).fill,
            })),
        })),
    };
}

export function createDoorPreview(svg, { metadata, labels, locale, onRender }) {
    let configuration = null;

    function render(nextConfiguration) {
        configuration = nextConfiguration;
        svg.replaceChildren();

        if (configuration === null) {
            onRender(false);
            return;
        }

        const { width, height } = svg.parentElement.getBoundingClientRect();
        let scene;
        try {
            scene = createPreviewScene(configuration, metadata, { width, height });
        } catch (error) {
            if (!(error instanceof RangeError)) {
                throw error;
            }
            onRender(false);
            return;
        }

        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svg.setAttribute('preserveAspectRatio', 'xMidYMid meet');
        drawScene(svg, scene, configuration, labels, locale);
        onRender(true);
    }

    const observer = new ResizeObserver(() => render(configuration));
    observer.observe(svg.parentElement);

    return {
        render,
        destroy() {
            observer.disconnect();
            svg.replaceChildren();
        },
    };
}

function drawScene(svg, scene, configuration, labels, locale) {
    const title = element(svg, 'title');
    title.textContent = labels.drawing_label;

    for (const door of scene.doors) {
        const group = element(svg, 'g', { 'data-door-index': door.index });
        for (const panel of door.panels) {
            element(group, 'rect', {
                ...rectangleAttributes(panel),
                fill: panel.fill,
                class: 'builder-preview-panel',
                'data-panel-index': panel.index,
            });
        }

        element(group, 'rect', {
            ...rectangleAttributes(door),
            fill: 'none',
            stroke: scene.frameFill,
            'stroke-width': door.frameWidth,
            class: 'builder-preview-frame',
        });
        for (const panel of door.panels.slice(1)) {
            line(group, door.x, panel.y, door.x + door.width, panel.y, {
                stroke: scene.frameFill,
                'stroke-width': Math.min(door.frameWidth / 2, panel.height / 4),
                class: 'builder-preview-divider',
            });
        }
    }

    drawDimensions(svg, scene.opening, configuration, labels, locale);
}

function drawDimensions(svg, opening, configuration, labels, locale) {
    const { x, y, width, height } = opening;
    const bottom = y + height;
    const right = x + width;
    const group = element(svg, 'g', { class: 'builder-preview-dimensions', 'aria-hidden': 'true' });
    line(group, x, bottom + 24, right, bottom + 24);
    line(group, x, bottom + 10, x, bottom + 29);
    line(group, right, bottom + 10, right, bottom + 29);
    line(group, x - 24, y, x - 24, bottom);
    line(group, x - 29, y, x - 10, y);
    line(group, x - 29, bottom, x - 10, bottom);

    const widthLabel = labels.width_annotation.replace(':value', formatDimension(configuration.openingWidth, locale));
    const heightLabel = labels.height_annotation.replace(':value', formatDimension(configuration.openingHeight, locale));
    text(group, x + width / 2, bottom + 40, widthLabel);
    text(group, x - 36, y + height / 2, heightLabel, {
        transform: `rotate(-90 ${x - 36} ${y + height / 2})`,
    });

    element(svg, 'desc').textContent = `${widthLabel}; ${heightLabel}`;
}

function formatDimension(value, locale) {
    return new Intl.NumberFormat(locale, {
        maximumSignificantDigits: 12,
        notation: value >= 1e8 || value < 0.001 ? 'scientific' : 'standard',
    }).format(value);
}

function rectangleAttributes({ x, y, width, height }) {
    return { x, y, width, height };
}

function line(parent, x1, y1, x2, y2, attributes = {}) {
    return element(parent, 'line', { x1, y1, x2, y2, stroke: 'currentColor', 'stroke-width': 1, ...attributes });
}

function text(parent, x, y, content, attributes = {}) {
    const label = element(parent, 'text', {
        x, y, 'text-anchor': 'middle', class: 'builder-preview-annotation', ...attributes,
    });
    label.textContent = content;
}

function element(parent, name, attributes = {}) {
    const node = parent.ownerDocument.createElementNS(SVG_NAMESPACE, name);
    for (const [key, value] of Object.entries(attributes)) {
        node.setAttribute(key, String(value));
    }
    parent.appendChild(node);
    return node;
}
